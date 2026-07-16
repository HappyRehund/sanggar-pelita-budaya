/* ============================================================
   Centralized animation utilities — GSAP-based
   Spec timing: short 150-250ms, medium 300-500ms, large 600-900ms
   ============================================================ */

import { gsap } from 'gsap';

const EASE_OUT = 'power2.out';
const EASE_SMOOTH = 'power3.out';

/**
 * Reveal an element on scroll using IntersectionObserver + GSAP.
 * Fires once when element enters viewport.
 * Returns a cleanup function.
 */
export function revealOnScroll(
  element: HTMLElement,
  options: {
    y?: number;
    duration?: number;
    delay?: number;
    threshold?: number;
    rootMargin?: string;
  } = {}
): () => void {
  const {
    y = 24,
    duration = 0.6,
    delay = 0,
    threshold = 0.15,
    rootMargin = '0px 0px -10% 0px',
  } = options;

  gsap.set(element, { opacity: 0, y });

  let hasRevealed = false;

  const observer = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (entry.isIntersecting && !hasRevealed) {
          hasRevealed = true;
          gsap.to(element, {
            opacity: 1,
            y: 0,
            duration,
            delay,
            ease: EASE_OUT,
          });
          observer.disconnect();
        }
      }
    },
    { threshold, rootMargin }
  );

  observer.observe(element);

  return () => observer.disconnect();
}

/**
 * Reveal multiple child elements with a stagger effect.
 * Parent element is observed; children animate in sequence when visible.
 */
export function staggerReveal(
  parent: HTMLElement,
  children: string,
  options: {
    y?: number;
    duration?: number;
    stagger?: number;
    delay?: number;
    threshold?: number;
  } = {}
): () => void {
  const {
    y = 20,
    duration = 0.5,
    stagger = 0.1,
    delay = 0,
    threshold = 0.15,
  } = options;

  const items = parent.querySelectorAll<HTMLElement>(children);
  gsap.set(items, { opacity: 0, y });

  let hasRevealed = false;

  const observer = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (entry.isIntersecting && !hasRevealed) {
          hasRevealed = true;
          gsap.to(items, {
            opacity: 1,
            y: 0,
            duration,
            stagger,
            delay,
            ease: EASE_SMOOTH,
          });
          observer.disconnect();
        }
      }
    },
    { threshold }
  );

  observer.observe(parent);

  return () => observer.disconnect();
}

/**
 * Apply parallax to a background element based on scroll position.
 * Returns a cleanup function that removes the scroll listener.
 */
export function parallax(
  element: HTMLElement,
  options: { speed?: number } = {}
): () => void {
  const { speed = 0.4 } = options;

  const handleScroll = () => {
    const offset = window.scrollY * speed;
    element.style.transform = `translateY(${offset}px)`;
  };

  handleScroll();
  window.addEventListener('scroll', handleScroll, { passive: true });

  return () => window.removeEventListener('scroll', handleScroll);
}

/**
 * Simple fade-in entrance for a container element.
 */
export function fadeInEntrance(
  element: HTMLElement,
  options: { duration?: number; delay?: number; y?: number } = {}
): void {
  const { duration = 0.4, delay = 0, y = 10 } = options;
  gsap.fromTo(
    element,
    { opacity: 0, y },
    { opacity: 1, y: 0, duration, delay, ease: EASE_OUT }
  );
}

/**
 * Check if user prefers reduced motion.
 */
export function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}