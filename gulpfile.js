const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sassGlob = require('gulp-sass-glob');
const sourceMaps = require('gulp-sourcemaps');
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const cleanCSS = require('gulp-clean-css');
const browserSync = require('browser-sync').create();
const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');

// Environment detection
const isDevelopment = process.env.NODE_ENV !== 'production';

const plumberNotify = (title) => {
	return {
		errorHandler: notify.onError({
			title: title,
			message: 'Error <%= error.message %>',
			sound: false,
		}),
	};
};

// Ensure js directory exists
function ensureJsDir() {
	if (!fs.existsSync('./js')) {
		fs.mkdirSync('./js');
		console.log('Created js directory');
	}
}

// ACF JSON timestamp update function
let isUpdating = false; // Флаг для запобігання нескінченному циклу

function updateAcfJsonTimestamp(filePath) {
	if (isUpdating) return; // Якщо вже оновлюємо, виходимо

	try {
		isUpdating = true;
		const data = JSON.parse(fs.readFileSync(filePath, 'utf8'));
		const currentTime = Math.floor(Date.now() / 1000);

		// Перевіряємо, чи timestamp дійсно потрібно оновити
		if (data.modified && data.modified >= currentTime - 1) {
			console.log('⏭️ Skipped ACF timestamp update (already recent):', path.basename(filePath));
			return;
		}

		data.modified = currentTime;
		fs.writeFileSync(filePath, JSON.stringify(data, null, 4) + '\n');
		console.log('✅ Updated ACF timestamp:', path.basename(filePath));
	} catch (error) {
		console.error('❌ Error updating ACF JSON:', error.message);
	} finally {
		// Скидаємо флаг через невелику затримку
		setTimeout(() => { isUpdating = false; }, 100);
	}
}

// WebP conversion settings
const WEBP_QUALITY = 80;
const WEBP_DIRECTORIES = ['./images'];
let webpConversionQueue = new Set();
let webpConversionTimeout = null;
let isWebpConverting = false;

// Function to convert single image to WebP
function convertImageToWebP(imagePath) {
	return new Promise((resolve, reject) => {
		const ext = path.extname(imagePath).toLowerCase();
		if (!['.jpg', '.jpeg', '.png'].includes(ext)) {
			resolve(false);
			return;
		}

		// Normalize path
		const normalizedPath = path.isAbsolute(imagePath) ? imagePath : path.resolve(process.cwd(), imagePath);
		const webpPath = normalizedPath.replace(/\.(jpg|jpeg|png)$/i, '.webp');
		
		// Check if original file exists
		if (!fs.existsSync(normalizedPath)) {
			resolve(false);
			return;
		}
		
		// Check if WebP exists and compare modification dates
		if (fs.existsSync(webpPath)) {
			const originalStat = fs.statSync(normalizedPath);
			const webpStat = fs.statSync(webpPath);
			
			// If original is newer than WebP, reconvert
			if (originalStat.mtimeMs > webpStat.mtimeMs) {
				// Delete old WebP to reconvert
				try {
					fs.unlinkSync(webpPath);
					console.log(`🔄 Re-converting: ${path.basename(normalizedPath)} (original is newer)`);
				} catch (deleteError) {
					console.error(`⚠️  Failed to delete old WebP: ${path.basename(webpPath)}`);
					resolve(false);
					return;
				}
			} else {
				// WebP is up to date, skip
				resolve(false);
				return;
			}
		}

		// Escape paths for shell command
		const escapedInput = normalizedPath.replace(/"/g, '\\"');
		const escapedOutput = webpPath.replace(/"/g, '\\"');

		// Convert using ffmpeg
		const command = `ffmpeg -i "${escapedInput}" -vframes 1 -quality ${WEBP_QUALITY} -compression_level 6 "${escapedOutput}" -y -loglevel error 2>/dev/null`;
		
		exec(command, (error) => {
			if (error) {
				console.error(`❌ WebP conversion failed: ${path.basename(normalizedPath)}`);
				reject(error);
				return;
			}

			if (fs.existsSync(webpPath)) {
				const originalSize = fs.statSync(normalizedPath).size;
				const webpSize = fs.statSync(webpPath).size;
				const reduction = Math.round(100 - (webpSize * 100 / originalSize));
				console.log(`✅ WebP: ${path.basename(normalizedPath)} -> ${path.basename(webpPath)} (економія: ~${reduction}%)`);
				
				// Delete original file after successful conversion
				try {
					fs.unlinkSync(normalizedPath);
				} catch (deleteError) {
					console.error(`⚠️  Failed to delete original: ${path.basename(normalizedPath)}`);
				}
				
				resolve(true);
			} else {
				reject(new Error('WebP file was not created'));
			}
		});
	});
}

// Process WebP conversion queue with debounce
function processWebpQueue() {
	if (isWebpConverting || webpConversionQueue.size === 0) {
		return;
	}

	isWebpConverting = true;
	const filesToConvert = Array.from(webpConversionQueue);
	webpConversionQueue.clear();

	Promise.all(filesToConvert.map(file => 
		convertImageToWebP(file).catch(() => false)
	)).then(() => {
		isWebpConverting = false;
		// Process remaining files if any were added during conversion
		if (webpConversionQueue.size > 0) {
			setTimeout(processWebpQueue, 500);
		}
	});
}

// Debounced WebP conversion handler
function queueWebpConversion(filePath) {
	// Normalize path to absolute
	const absolutePath = path.isAbsolute(filePath) ? filePath : path.resolve(process.cwd(), filePath);
	
	// Skip if original file doesn't exist
	if (!fs.existsSync(absolutePath)) {
		return;
	}
	
	// Skip if already in queue
	if (webpConversionQueue.has(absolutePath)) {
		return;
	}
	
	// Check if WebP exists and compare modification dates
	const webpPath = absolutePath.replace(/\.(jpg|jpeg|png)$/i, '.webp');
	if (fs.existsSync(webpPath)) {
		const originalStat = fs.statSync(absolutePath);
		const webpStat = fs.statSync(webpPath);
		
		// If WebP is up to date, skip
		if (originalStat.mtimeMs <= webpStat.mtimeMs) {
			return;
		}
		// If original is newer, it will be reconverted in convertImageToWebP
	}
	
	webpConversionQueue.add(absolutePath);
	
	if (webpConversionTimeout) {
		clearTimeout(webpConversionTimeout);
	}
	
	webpConversionTimeout = setTimeout(() => {
		processWebpQueue();
	}, 1000); // 1 second debounce
}

// SCSS compile task
gulp.task('sass:wp', function () {
	let stream = gulp
		.src('./scss/*.scss')
		.pipe(plumber(plumberNotify('SCSS')));
	
	// Add source maps only in development
	if (isDevelopment) {
		stream = stream.pipe(sourceMaps.init());
	}
	
	stream = stream
		.pipe(sassGlob())
		.pipe(sass({
			silenceDeprecations: ['import', 'legacy-js-api']
		}))
		.pipe(cleanCSS({
			compatibility: 'ie8',
			level: 2
		}));
	
	// Write source maps only in development
	if (isDevelopment) {
		stream = stream.pipe(sourceMaps.write());
	}
	
	return stream
		.pipe(gulp.dest('./css/'))
		.pipe(browserSync.stream()); // Inject styles without full reload
});

// JavaScript tasks
gulp.task('js:animation', function () {
	ensureJsDir();
	let stream = gulp
		.src('./scripts/animation.js')
		.pipe(plumber(plumberNotify('JS Animation')));
	
	// Add source maps only in development
	if (isDevelopment) {
		stream = stream.pipe(sourceMaps.init());
	}
	
	stream = stream.pipe(uglify());
	
	// Write source maps only in development
	if (isDevelopment) {
		stream = stream.pipe(sourceMaps.write());
	}
	
	return stream
		.pipe(gulp.dest('./js/'))
		.pipe(browserSync.stream());
});

gulp.task('js:main', function () {
	ensureJsDir();
	let stream = gulp
		.src([
			'./scripts/main.js',
			'./scripts/components/*.js'
		])
		.pipe(plumber(plumberNotify('JS Main')));
	
	// Add source maps only in development
	if (isDevelopment) {
		stream = stream.pipe(sourceMaps.init());
	}
	
	stream = stream
		.pipe(concat('main.js'))
		.pipe(uglify());
	
	// Write source maps only in development
	if (isDevelopment) {
		stream = stream.pipe(sourceMaps.write());
	}
	
	return stream
		.pipe(gulp.dest('./js/'))
		.pipe(browserSync.stream());
});

gulp.task('js:vendor', function () {
	ensureJsDir();
	let stream = gulp
		.src('./scripts/vendor/*.js')
		.pipe(plumber(plumberNotify('JS Vendor')));
	
	// Add source maps only in development
	if (isDevelopment) {
		stream = stream.pipe(sourceMaps.init());
	}
	
	stream = stream
		.pipe(concat('vendor.js'))
		.pipe(uglify());
	
	// Write source maps only in development
	if (isDevelopment) {
		stream = stream.pipe(sourceMaps.write());
	}
	
	return stream
		.pipe(gulp.dest('./js/'))
		.pipe(browserSync.stream());
});

// Recursive function to find image files
function findImageFiles(dir, fileList = []) {
	const files = fs.readdirSync(dir);
	
	files.forEach(file => {
		const filePath = path.join(dir, file);
		const stat = fs.statSync(filePath);
		
		if (stat.isDirectory()) {
			findImageFiles(filePath, fileList);
		} else if (/\.(jpg|jpeg|png)$/i.test(file)) {
			fileList.push(filePath);
		}
	});
	
	return fileList;
}

// WebP conversion task
gulp.task('images:webp', function (cb) {
	// Check if ffmpeg is available
	exec('which ffmpeg', (error) => {
		if (error) {
			console.log('⚠️  ffmpeg not found. Skipping WebP conversion.');
			cb();
			return;
		}

		const allPromises = [];
		const allImageFiles = [];
		
		WEBP_DIRECTORIES.forEach(dir => {
			if (!fs.existsSync(dir)) {
				console.log(`⚠️  Directory ${dir} does not exist. Skipping.`);
				return;
			}

			const files = findImageFiles(dir);
			allImageFiles.push(...files);

			files.forEach(file => {
				const webpFile = file.replace(/\.(jpg|jpeg|png)$/i, '.webp');
				
				// Check if WebP needs to be created or updated
				if (!fs.existsSync(webpFile)) {
					// WebP doesn't exist, convert
					allPromises.push(convertImageToWebP(file).catch(() => false));
				} else {
					// WebP exists, check if original is newer
					const originalStat = fs.statSync(file);
					const webpStat = fs.statSync(webpFile);
					
					if (originalStat.mtimeMs > webpStat.mtimeMs) {
						// Original is newer, reconvert
						allPromises.push(convertImageToWebP(file).catch(() => false));
					}
				}
			});
		});

		// Check if any JPG/JPEG/PNG files exist
		if (allImageFiles.length === 0) {
			console.log('ℹ️  No JPG, JPEG, or PNG files found in watched directories.');
			cb();
			return;
		}

		if (allPromises.length === 0) {
			console.log(`✅ All ${allImageFiles.length} image(s) already converted to WebP`);
			cb();
			return;
		}

		console.log(`🔄 Converting ${allPromises.length} of ${allImageFiles.length} image(s) to WebP...`);
		Promise.all(allPromises).then(() => {
			console.log('✅ WebP conversion completed');
			cb();
		}).catch(() => {
			cb();
		});
	});
});

// BrowserSync with PHP proxy
gulp.task('browser-sync:wp', function () {
	browserSync.init({
		proxy: 'http://rgb.local/',
		https: false, // Використовуємо HTTP за замовчуванням
		port: 3000,
		notify: false,
		open: false,
		files: [
			'./**/*.php',
			'./js/**/*.js',
			'./css/**/*.css',
		],
	});

	// Watch SCSS and recompile
	gulp.watch('./scss/**/*.scss', gulp.series('sass:wp'));

	// Watch JavaScript files
	gulp.watch('./scripts/animation.js', gulp.series('js:animation'));
	gulp.watch('./scripts/main.js', gulp.series('js:main'));
	gulp.watch('./scripts/components/*.js', gulp.series('js:main'));
	gulp.watch('./scripts/vendor/*.js', gulp.series('js:vendor'));

	// Watch ACF JSON files and update timestamp
	gulp.watch('./acf-json/*.json').on('change', function(filePath) {
		updateAcfJsonTimestamp(filePath);
	});

	// Watch images for WebP conversion (with debounce)
	WEBP_DIRECTORIES.forEach(dir => {
		if (fs.existsSync(dir)) {
			gulp.watch(`${dir}/**/*.{jpg,jpeg,png}`).on('add', function(filePath) {
				queueWebpConversion(filePath);
			});
		}
	});
});

// Watch task for development
gulp.task('watch', function () {
	gulp.watch('./scss/**/*.scss', gulp.series('sass:wp'));
	gulp.watch('./scripts/animation.js', gulp.series('js:animation'));
	gulp.watch('./scripts/main.js', gulp.series('js:main'));
	gulp.watch('./scripts/components/*.js', gulp.series('js:main'));
	gulp.watch('./scripts/vendor/*.js', gulp.series('js:vendor'));

	// Watch ACF JSON files and update timestamp
	gulp.watch('./acf-json/*.json').on('change', function (filePath) {
		updateAcfJsonTimestamp(filePath);
	});

	// Watch images for WebP conversion (with debounce)
	WEBP_DIRECTORIES.forEach(dir => {
		if (fs.existsSync(dir)) {
			gulp.watch(`${dir}/**/*.{jpg,jpeg,png}`).on('add', function(filePath) {
				queueWebpConversion(filePath);
			});
		}
	});
});

// ACF JSON timestamp update task
gulp.task('acf:update', function (cb) {
	const files = fs.readdirSync('./acf-json').filter(f => f.endsWith('.json'));
	files.forEach(file => {
		updateAcfJsonTimestamp(path.join('./acf-json', file));
	});
	cb();
});

// Build task for production
gulp.task('build', gulp.series('sass:wp', 'js:animation', 'js:main', 'js:vendor'));

// Main Gulp task
gulp.task('wp', gulp.series('images:webp', 'sass:wp', 'js:animation', 'js:main', 'js:vendor', 'browser-sync:wp'));
