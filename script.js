// Initialize EmailJS
(function() {
    emailjs.init("CDN3p6l0QoJvNWnI0");
})();

// Hero Slider
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slide');

function nextSlide() {
    slides[currentSlide].classList.remove('active');
    currentSlide = (currentSlide + 1) % slides.length;
    slides[currentSlide].classList.add('active');
}

setInterval(nextSlide, 5000);

// News Videos Control
document.addEventListener('DOMContentLoaded', function() {
    const newsCards = document.querySelectorAll('.news-card');
    
    newsCards.forEach((card, index) => {
        const video = card.querySelector('video');
        
        if (video) {
            if (index === 0) {
                video.play().catch(e => console.log('Autoplay prevented:', e));
            }
            
            card.addEventListener('mouseenter', function() {
                newsCards.forEach((otherCard, otherIndex) => {
                    if (otherIndex !== index) {
                        const otherVideo = otherCard.querySelector('video');
                        if (otherVideo) {
                            otherVideo.pause();
                        }
                    }
                });
                
                video.play().catch(e => console.log('Play prevented:', e));
            });
            
            card.addEventListener('mouseleave', function() {
                if (index !== 0) {
                    video.pause();
                }
            });
        }
    });
});

// Parallax Effect
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    
    const heroContent = document.getElementById('heroContent');
    if (heroContent) {
        heroContent.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
    
    const parallaxBgs = document.querySelectorAll('.parallax-bg');
    parallaxBgs.forEach(bg => {
        const speed = 0.3;
        const rect = bg.parentElement.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            bg.style.transform = `translateY(${(rect.top - window.innerHeight) * speed}px)`;
        }
    });
});

// Animated Counters
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target + '+';
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
            entry.target.classList.add('animated');
            animateCounter(entry.target);
        }
    });
}, { threshold: 0.5 });

document.querySelectorAll('.stat-number.counting').forEach(counter => {
    counterObserver.observe(counter);
});

// Page Navigation
function showPage(pageName) {
    const pages = document.querySelectorAll('.page-content');
    pages.forEach(page => {
        page.classList.remove('active');
    });
    document.getElementById(pageName).classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Mobile Menu
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.toggle('active');
}

function closeMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.remove('active');
}

document.addEventListener('click', function(event) {
    const mobileMenu = document.getElementById('mobileMenu');
    const menuBtn = document.querySelector('.mobile-menu-btn');
    
    if (mobileMenu.classList.contains('active') && 
        !mobileMenu.contains(event.target) && 
        !menuBtn.contains(event.target)) {
        mobileMenu.classList.remove('active');
    }
});

// Countdown Timer
function updateCountdown() {
    const eventDate = new Date('2025-10-28T18:00:00').getTime();
    const now = new Date().getTime();
    const distance = eventDate - now;

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById('days').textContent = days.toString().padStart(2, '0');
    document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
    document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
    document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
}

setInterval(updateCountdown, 1000);
updateCountdown();

// Contact Form Handler with EmailJS
function handleContactSubmit(event) {
    event.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const successAlert = document.getElementById('successAlert');
    const errorAlert = document.getElementById('errorAlert');
    
    successAlert.classList.remove('show');
    errorAlert.classList.remove('show');
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending... ⏳';

    const params = {
        name: document.getElementById("name").value,
        email: document.getElementById("email").value,
        subject: document.getElementById("subject").value,
        message: document.getElementById("message").value
    };

    emailjs.send('service_qeyag2o', 'template_smzv04f', params)
        .then(function(response) {
            console.log('✅ SUCCESS!', response.status, response.text);
            
            successAlert.classList.add('show');
            event.target.reset();
            
            submitBtn.disabled = false;
            submitBtn.textContent = 'Send ✉️';
            
            setTimeout(() => {
                successAlert.classList.remove('show');
            }, 5000);
            
        }, function(error) {
            console.error('❌ FAILED...', error);
            
            errorAlert.classList.add('show');
            
            submitBtn.disabled = false;
            submitBtn.textContent = 'Send ✉️';
            
            setTimeout(() => {
                errorAlert.classList.remove('show');
            }, 5000);
        });
}

// Video Modal Functions
function openVideoModal(videoSrc) {
    const modal = document.getElementById('videoModal');
    const video = document.getElementById('modalVideo');
    video.src = videoSrc;
    modal.classList.add('active');
    video.play();
}

function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    const video = document.getElementById('modalVideo');
    modal.classList.remove('active');
    video.pause();
    video.currentTime = 0;
    video.src = '';
}

document.getElementById('videoModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeVideoModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeVideoModal();
    }
});

// Smooth scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.querySelectorAll('.stat-card, .team-card, .video-card, .event-card, .news-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'all 0.6s ease';
    observer.observe(el);
});