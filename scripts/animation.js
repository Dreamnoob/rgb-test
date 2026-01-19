// --- Lenis Smooth Scroll Setup ---
// Initialize a new Lenis instance for smooth scrolling
const lenis = new Lenis({
	duration: 1, // Час анімації скролу (секунди)
	smooth: true,
});

gsap.registerPlugin(ScrollTrigger);

// Synchronize Lenis scrolling with GSAP's ScrollTrigger plugin
lenis.on('scroll', ScrollTrigger.update);

// Add Lenis's requestAnimationFrame (raf) method to GSAP's ticker
// This ensures Lenis's smooth scroll animation updates on each GSAP tick
gsap.ticker.add((time) => {
	lenis.raf(time * 1000); // Convert time from seconds to milliseconds
});

// Disable lag smoothing in GSAP to prevent any delay in scroll animations
gsap.ticker.lagSmoothing(0);



function initAnimations() {

	// --- 1. Індивідуальні елементи ---

	// Масив виключень для спеціальних анімацій
	const excludedAnimations = ['parallax'];

	// Фільтруємо елементи, виключаючи спеціальні анімації
	const generalAnimateElements = gsap.utils.toArray('[data-animate]').filter(el =>
		!excludedAnimations.includes(el.dataset.animate)
	);

	generalAnimateElements.forEach((el) => {
		const animationType = el.dataset.animate;

		const delay = parseFloat(el.dataset.delay) || 0;
		const duration = parseFloat(el.dataset.duration) || 1;

		let animationProps = {
			opacity: 1,
			duration,
			delay,
			ease: "power2.out"
		};

		switch (animationType) {
			case 'fade-up':
				animationProps.y = 0;
				break;
			case 'slide-left':
				animationProps.x = 0;
				break;
			case 'slide-right':
				animationProps.x = 0;
				break;
			case 'zoom-in':
				animationProps.scale = 1;
				break;
			case 'fade':
				// тільки opacity
				break;
			case 'rotate-in':
				animationProps.rotate = 0;
				animationProps.scale = 1;
				break;
			case 'grow-fade':
				animationProps.scale = 1;
				break;
			case 'flip-y':
				animationProps.rotateY = 0;
				break;
			case 'bounce-in':
				animationProps.y = 0;
				animationProps.scale = 1;
				animationProps.ease = "bounce.out";
				break;
			case 'blind-down':
				animationProps.clipPath = "polygon(0 0, 100% 0, 100% 100%, 0 100%)";
				break;
			case 'text-fade':
			case 'text-line':
			case 'text-word':
			case 'text-char':
				// SplitText анімації будуть оброблятися окремо
				return;
		}

		gsap.fromTo(el,
			{}, // початкові значення в CSS
			{
				...animationProps,
				scrollTrigger: {
					trigger: el,
					start: 'top 90%',
					toggleActions: 'play none none none',
					once: false
				}
			}
		);
	});


	// --- 2. Групові анімації з data-animate-group ---
	gsap.utils.toArray('[data-animate-group]').forEach((groupEl) => {
		const animationType = groupEl.dataset.animateGroup;
		const delay = parseFloat(groupEl.dataset.delay) || 0;
		const duration = parseFloat(groupEl.dataset.duration) || 1;
		const stagger = parseFloat(groupEl.dataset.stagger) || 0.2;

		const children = Array.from(groupEl.children);

		let fromVars = {};
		let toVars = {
			opacity: 1,
			duration,
			delay,
			ease: 'power2.out',
			stagger,
			scrollTrigger: {
				trigger: groupEl,
				start: 'top 90%',
				toggleActions: 'play none none none',
				once: false
			}
		};

		switch (animationType) {
			case 'fade-up':
				fromVars = { opacity: 0, y: 40 };
				toVars.y = 0;
				break;
			case 'slide-left':
				fromVars = { opacity: 0, x: -40 };
				toVars.x = 0;
				break;
			case 'slide-right':
				fromVars = { opacity: 0, x: 40 };
				toVars.x = 0;
				break;
			case 'zoom-in':
				fromVars = { opacity: 0, scale: 0.95 };
				toVars.scale = 1;
				break;
			case 'fade':
				fromVars = { opacity: 0 };
				break;
			case 'rotate-in':
				fromVars = { opacity: 0, rotate: -10, scale: 0.95 };
				toVars.rotate = 0;
				toVars.scale = 1;
				break;
			case 'grow-fade':
				fromVars = { opacity: 0, scale: 1.1 };
				toVars.scale = 1;
				break;
			case 'flip-y':
				fromVars = { opacity: 0, rotateY: 60, transformPerspective: 400 };
				toVars.rotateY = 0;
				break;
			case 'bounce-in':
				fromVars = { opacity: 0, scale: 0.8, y: 40 };
				toVars.scale = 1;
				toVars.y = 0;
				toVars.ease = "bounce.out";
				break;
			case 'blind-down':
				fromVars = { opacity: 1, clipPath: "polygon(0 0, 100% 0, 100% 0, 0 0)" };
				toVars.clipPath = "polygon(0 0, 100% 0, 100% 100%, 0 100%)";
				break;
		}

		// Використовуємо gsap.to для кращої роботи з stagger
		gsap.to(children, {
			...toVars,
			stagger,
			scrollTrigger: {
				trigger: groupEl,
				start: 'top 90%',
				toggleActions: 'play none none none',
				once: false
			}
		});


	});

	gsap.utils.toArray('[data-animate="parallax"]').forEach((el) => {
		const img = el.querySelector('img');
		const video = el.querySelector('video');
		const target = img || video;

		if (target) {

			gsap.fromTo(target, {
				y: -50,
			}, {
				y: 50,
				ease: "none",
				scrollTrigger: {
					trigger: el,
					start: "top bottom",
					end: "bottom top",
					scrub: .1
				}
			});
		}
	});
}

initAnimations();

