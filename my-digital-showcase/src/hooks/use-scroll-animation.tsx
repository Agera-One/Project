import { useInView } from 'react-intersection-observer';

interface UseScrollAnimationOptions {
  threshold?: number;
  triggerOnce?: boolean;
  delay?: number;
}

export const useScrollAnimation = (options: UseScrollAnimationOptions = {}) => {
  const { threshold = 0.1, triggerOnce = true, delay = 0 } = options;

  const { ref, inView } = useInView({
    threshold,
    triggerOnce,
  });

  return {
    ref,
    inView,
    style: {
      transitionDelay: `${delay}ms`,
    },
  };
};
