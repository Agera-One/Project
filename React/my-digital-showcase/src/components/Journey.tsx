import { Card } from "@/components/ui/card";
import { CheckCircle2 } from "lucide-react";
import { useScrollAnimation } from "@/hooks/use-scroll-animation";

const Journey = () => {
  const titleAnimation = useScrollAnimation({ delay: 100 });
  const descAnimation = useScrollAnimation({ delay: 200 });

  const milestones = [
    {
      year: "2024",
      title: "Advanced Frontend Development",
      description:
        "Mastered React ecosystem, TypeScript, and modern build tools. Focused on performance optimization and accessibility.",
      achievements: ["React Hooks & Context", "TypeScript", "Tailwind CSS", "Vite"],
    },
    {
      year: "2023",
      title: "Intermediate Development",
      description:
        "Expanded skills in JavaScript frameworks and responsive design. Built multiple production-ready projects.",
      achievements: ["React Fundamentals", "API Integration", "Git Workflow", "CSS Frameworks"],
    },
    {
      year: "2022",
      title: "Foundation Building",
      description:
        "Started web development journey with HTML, CSS, and JavaScript. Completed foundational courses and projects.",
      achievements: ["HTML5 & CSS3", "JavaScript ES6+", "Responsive Design", "Web Fundamentals"],
    },
  ];

  return (
    <section id="journey" className="py-20 bg-background">
      <div className="container mx-auto px-4">
        <div className="max-w-4xl mx-auto">
          <h2 
            ref={titleAnimation.ref}
            className={`text-4xl md:text-5xl font-bold mb-6 text-center transition-all duration-700 ${
              titleAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={titleAnimation.style}
          >
            My Journey
          </h2>
          <p 
            ref={descAnimation.ref}
            className={`text-lg text-muted-foreground mb-12 text-center transition-all duration-700 ${
              descAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={descAnimation.style}
          >
            The path of continuous learning and skill development
          </p>

          <div className="relative">
            {/* Timeline line */}
            <div className="absolute left-8 md:left-1/2 top-0 bottom-0 w-0.5 bg-accent/30" />

            <div className="space-y-12">
              {milestones.map((milestone, index) => {
                const milestoneAnimation = useScrollAnimation({ delay: index * 150 });
                return (
                  <div
                    key={index}
                    ref={milestoneAnimation.ref}
                    className={`relative flex flex-col md:flex-row gap-8 items-start md:items-center transition-all duration-700 ${
                      milestoneAnimation.inView 
                        ? 'opacity-100 translate-x-0' 
                        : index % 2 === 0 
                          ? 'opacity-0 -translate-x-8' 
                          : 'opacity-0 translate-x-8'
                    }`}
                    style={milestoneAnimation.style}
                  >
                    {/* Year indicator */}
                    <div className="absolute left-8 md:left-1/2 -translate-x-1/2 w-16 h-16 rounded-full bg-accent flex items-center justify-center shadow-medium z-10 group hover:scale-110 transition-transform">
                      <span className="text-accent-foreground font-bold">{milestone.year}</span>
                    </div>

                    {/* Content card */}
                    <Card
                      className={`flex-1 p-6 shadow-soft hover-lift ml-20 md:ml-0 ${
                        index % 2 === 0 ? "md:mr-[calc(50%+4rem)]" : "md:ml-[calc(50%+4rem)]"
                      }`}
                    >
                      <h3 className="text-2xl font-semibold mb-2 hover:text-accent transition-colors">{milestone.title}</h3>
                      <p className="text-muted-foreground mb-4">{milestone.description}</p>
                      <div className="space-y-2">
                        {milestone.achievements.map((achievement, achievementIndex) => (
                          <div key={achievementIndex} className="flex items-center gap-2 group">
                            <CheckCircle2 className="w-5 h-5 text-accent flex-shrink-0 group-hover:scale-110 transition-transform" />
                            <span className="text-sm group-hover:text-accent transition-colors">{achievement}</span>
                          </div>
                        ))}
                      </div>
                    </Card>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Journey;
