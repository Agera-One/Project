import { ExternalLink, Github } from "lucide-react";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { useScrollAnimation } from "@/hooks/use-scroll-animation";

const Projects = () => {
  const titleAnimation = useScrollAnimation({ delay: 100 });
  const descAnimation = useScrollAnimation({ delay: 200 });

  const projects = [
    {
      title: "E-Commerce Platform",
      description:
        "A fully responsive online shopping platform with cart functionality, product filtering, and checkout process.",
      tags: ["React", "TypeScript", "Tailwind CSS"],
      github: "#",
      live: "#",
    },
    {
      title: "Task Management App",
      description:
        "Intuitive task organizer with drag-and-drop functionality, categories, and priority levels.",
      tags: ["React", "JavaScript", "CSS"],
      github: "#",
      live: "#",
    },
    {
      title: "Weather Dashboard",
      description:
        "Real-time weather information with beautiful visualizations and 7-day forecasts.",
      tags: ["React", "API Integration", "Charts"],
      github: "#",
      live: "#",
    },
    {
      title: "Portfolio Template",
      description:
        "Modern, customizable portfolio template for developers and designers.",
      tags: ["HTML", "CSS", "JavaScript"],
      github: "#",
      live: "#",
    },
  ];

  return (
    <section id="projects" className="py-20 gradient-subtle">
      <div className="container mx-auto px-4">
        <div className="max-w-6xl mx-auto">
          <h2 
            ref={titleAnimation.ref}
            className={`text-4xl md:text-5xl font-bold mb-6 text-center transition-all duration-700 ${
              titleAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={titleAnimation.style}
          >
            Featured Projects
          </h2>
          <p 
            ref={descAnimation.ref}
            className={`text-lg text-muted-foreground mb-12 text-center transition-all duration-700 ${
              descAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={descAnimation.style}
          >
            A selection of projects showcasing my skills and experience
          </p>

          <div className="grid md:grid-cols-2 gap-8">
            {projects.map((project, index) => {
              const projectAnimation = useScrollAnimation({ delay: index * 100 });
              return (
                <Card
                  key={index}
                  ref={projectAnimation.ref}
                  className={`p-6 shadow-soft hover-lift group transition-all duration-700 ${
                    projectAnimation.inView ? 'opacity-100 scale-100' : 'opacity-0 scale-95'
                  }`}
                  style={projectAnimation.style}
                >
                  <h3 className="text-2xl font-semibold mb-3 group-hover:text-accent transition-colors">{project.title}</h3>
                  <p className="text-muted-foreground mb-4">{project.description}</p>
                  <div className="flex flex-wrap gap-2 mb-6">
                    {project.tags.map((tag, tagIndex) => (
                      <Badge key={tagIndex} variant="secondary" className="hover:scale-110 transition-transform">
                        {tag}
                      </Badge>
                    ))}
                  </div>
                  <div className="flex gap-4">
                    <Button
                      variant="outline"
                      size="sm"
                      className="flex items-center gap-2 hover:scale-105 transition-transform"
                      asChild
                    >
                      <a href={project.github} target="_blank" rel="noopener noreferrer">
                        <Github size={16} />
                        Code
                      </a>
                    </Button>
                    <Button
                      size="sm"
                      className="flex items-center gap-2 bg-accent hover:bg-accent/90 hover:scale-105 transition-all"
                      asChild
                    >
                      <a href={project.live} target="_blank" rel="noopener noreferrer">
                        <ExternalLink size={16} />
                        Live Demo
                      </a>
                    </Button>
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

export default Projects;
