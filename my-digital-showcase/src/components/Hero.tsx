import { ArrowDown } from "lucide-react";
import { Button } from "@/components/ui/button";
import heroBg from "@/assets/hero-bg.jpg";
import { useScrollAnimation } from "@/hooks/use-scroll-animation";

const Hero = () => {
  const titleAnimation = useScrollAnimation({ delay: 100 });
  const subtitleAnimation = useScrollAnimation({ delay: 300 });
  const buttonsAnimation = useScrollAnimation({ delay: 500 });

  return (
    <section
      id="hero"
      className="min-h-screen flex items-center justify-center relative overflow-hidden"
    >
      {/* Background */}
      <div className="absolute inset-0 z-0">
        <img
          src={heroBg}
          alt=""
          className="w-full h-full object-cover opacity-20"
        />
        <div className="absolute inset-0 gradient-hero opacity-90" />
      </div>

      {/* Floating elements for visual interest */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div className="absolute top-20 left-10 w-32 h-32 bg-accent/10 rounded-full blur-3xl animate-float" />
        <div className="absolute bottom-20 right-10 w-40 h-40 bg-highlight/10 rounded-full blur-3xl animate-float" style={{ animationDelay: "1s" }} />
      </div>

      {/* Content */}
      <div className="container mx-auto px-4 z-10 text-center">
        <div className="max-w-4xl mx-auto">
          <h1 
            ref={titleAnimation.ref}
            className={`text-5xl md:text-7xl font-bold mb-6 text-primary-foreground transition-all duration-700 ${
              titleAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={titleAnimation.style}
          >
            Frontend Developer
          </h1>
          <p 
            ref={subtitleAnimation.ref}
            className={`text-xl md:text-2xl mb-8 text-primary-foreground/90 transition-all duration-700 ${
              subtitleAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={subtitleAnimation.style}
          >
            Crafting beautiful, responsive, and user-focused web experiences
          </p>
          <div 
            ref={buttonsAnimation.ref}
            className={`flex flex-col sm:flex-row gap-4 justify-center transition-all duration-700 ${
              buttonsAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={buttonsAnimation.style}
          >
            <Button
              size="lg"
              className="bg-accent hover:bg-accent/90 text-accent-foreground hover:scale-105 transition-all duration-300 hover:shadow-strong"
              asChild
            >
              <a href="#projects">View My Work</a>
            </Button>
            <Button
              size="lg"
              variant="outline"
              className="border-primary-foreground/30 text-primary-foreground hover:bg-primary-foreground/10 hover:scale-105 transition-all duration-300 hover:border-primary-foreground/50"
              asChild
            >
              <a href="#contact">Get In Touch</a>
            </Button>
          </div>
        </div>
      </div>

      {/* Scroll Indicator */}
      <a
        href="#about"
        className="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce text-primary-foreground/70 hover:text-primary-foreground transition-smooth hover:scale-110"
      >
        <ArrowDown size={32} />
      </a>
    </section>
  );
};

export default Hero;
