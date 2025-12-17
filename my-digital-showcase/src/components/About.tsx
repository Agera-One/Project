import { Code2, Palette, Zap } from "lucide-react";
import { Card } from "@/components/ui/card";
import { useScrollAnimation } from "@/hooks/use-scroll-animation";

const About = () => {
  const titleAnimation = useScrollAnimation({ delay: 100 });
  const descAnimation = useScrollAnimation({ delay: 200 });
  const bioAnimation = useScrollAnimation({ delay: 300 });

  const highlights = [
    {
      icon: Code2,
      title: "Clean Code",
      description: "Writing maintainable, scalable, and efficient code",
    },
    {
      icon: Palette,
      title: "Design Focus",
      description: "Creating visually appealing and intuitive interfaces",
    },
    {
      icon: Zap,
      title: "Performance",
      description: "Optimizing for speed and user experience",
    },
  ];

  return (
    <section id="about" className="py-20 gradient-subtle">
      <div className="container mx-auto px-4">
        <div className="max-w-4xl mx-auto">
          <h2 
            ref={titleAnimation.ref}
            className={`text-4xl md:text-5xl font-bold mb-6 text-center transition-all duration-700 ${
              titleAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={titleAnimation.style}
          >
            About Me
          </h2>
          <p 
            ref={descAnimation.ref}
            className={`text-lg text-muted-foreground mb-12 text-center transition-all duration-700 ${
              descAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={descAnimation.style}
          >
            Passionate frontend developer dedicated to creating exceptional web experiences
            through modern technologies and best practices.
          </p>

          <div className="grid md:grid-cols-3 gap-6 mb-12">
            {highlights.map((item, index) => {
              const animation = useScrollAnimation({ delay: 100 * (index + 1) });
              return (
                <Card
                  key={index}
                  ref={animation.ref}
                  className={`p-6 shadow-soft hover-lift group transition-all duration-700 ${
                    animation.inView ? 'opacity-100 scale-100' : 'opacity-0 scale-95'
                  }`}
                  style={animation.style}
                >
                  <item.icon className="w-12 h-12 mb-4 text-accent transition-all duration-300 group-hover:scale-110 group-hover:rotate-6" />
                  <h3 className="text-xl font-semibold mb-2 transition-colors group-hover:text-accent">{item.title}</h3>
                  <p className="text-muted-foreground">{item.description}</p>
                </Card>
              );
            })}
          </div>

          <Card 
            ref={bioAnimation.ref}
            className={`p-8 shadow-medium hover-lift transition-all duration-700 ${
              bioAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={bioAnimation.style}
          >
            <p className="text-lg leading-relaxed">
              I'm a frontend developer with a passion for building beautiful, functional,
              and accessible web applications. My journey in web development has been driven
              by curiosity and a commitment to continuous learning. I specialize in modern
              JavaScript frameworks and have a keen eye for design details that make the
              difference between good and great user experiences.
            </p>
          </Card>
        </div>
      </div>
    </section>
  );
};

export default About;
