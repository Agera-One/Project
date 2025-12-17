import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { useScrollAnimation } from "@/hooks/use-scroll-animation";

const Skills = () => {
  const titleAnimation = useScrollAnimation({ delay: 100 });
  const descAnimation = useScrollAnimation({ delay: 200 });

  const skillCategories = [
    {
      category: "Frontend",
      skills: [
        { name: "React", level: 90 },
        { name: "TypeScript", level: 85 },
        { name: "JavaScript", level: 95 },
        { name: "HTML/CSS", level: 95 },
        { name: "Tailwind CSS", level: 90 },
      ],
    },
    {
      category: "Tools & Others",
      skills: [
        { name: "Git", level: 85 },
        { name: "Vite", level: 80 },
        { name: "Responsive Design", level: 95 },
        { name: "UI/UX Principles", level: 85 },
        { name: "Web Performance", level: 80 },
      ],
    },
  ];

  return (
    <section id="skills" className="py-20 bg-background">
      <div className="container mx-auto px-4">
        <div className="max-w-4xl mx-auto">
          <h2 
            ref={titleAnimation.ref}
            className={`text-4xl md:text-5xl font-bold mb-6 text-center transition-all duration-700 ${
              titleAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={titleAnimation.style}
          >
            Technical Skills
          </h2>
          <p 
            ref={descAnimation.ref}
            className={`text-lg text-muted-foreground mb-12 text-center transition-all duration-700 ${
              descAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={descAnimation.style}
          >
            Technologies and tools I work with to bring ideas to life
          </p>

          <div className="grid md:grid-cols-2 gap-8">
            {skillCategories.map((category, categoryIndex) => {
              const cardAnimation = useScrollAnimation({ delay: categoryIndex * 200 });
              return (
                <Card
                  key={categoryIndex}
                  ref={cardAnimation.ref}
                  className={`p-6 shadow-soft hover-lift transition-all duration-700 ${
                    cardAnimation.inView ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-8'
                  }`}
                  style={cardAnimation.style}
                >
                  <h3 className="text-2xl font-semibold mb-6 text-accent">
                    {category.category}
                  </h3>
                  <div className="space-y-4">
                    {category.skills.map((skill, skillIndex) => (
                      <div key={skillIndex} className="group">
                        <div className="flex justify-between mb-2">
                          <span className="font-medium group-hover:text-accent transition-colors">{skill.name}</span>
                          <Badge variant="secondary" className="group-hover:scale-110 transition-transform">{skill.level}%</Badge>
                        </div>
                        <div className="h-2 bg-secondary rounded-full overflow-hidden">
                          <div
                            className={`h-full bg-gradient-accent transition-all duration-1000 ease-out ${
                              cardAnimation.inView ? '' : 'w-0'
                            }`}
                            style={{
                              width: cardAnimation.inView ? `${skill.level}%` : '0%',
                              transitionDelay: `${skillIndex * 100}ms`,
                            }}
                          />
                        </div>
                      </div>
                    ))}
                  </div>
                </Card>
              );
            })}
          </div>
        </div>
      </div>
    </section>
  );
};

export default Skills;
