import { Mail, Linkedin, Github, Twitter } from "lucide-react";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { useScrollAnimation } from "@/hooks/use-scroll-animation";

const Contact = () => {
  const titleAnimation = useScrollAnimation({ delay: 100 });
  const descAnimation = useScrollAnimation({ delay: 200 });
  const cardAnimation = useScrollAnimation({ delay: 300 });

  const contactMethods = [
    {
      icon: Mail,
      label: "Email",
      value: "your.email@example.com",
      href: "mailto:your.email@example.com",
    },
    {
      icon: Linkedin,
      label: "LinkedIn",
      value: "linkedin.com/in/yourprofile",
      href: "https://linkedin.com/in/yourprofile",
    },
    {
      icon: Github,
      label: "GitHub",
      value: "github.com/yourusername",
      href: "https://github.com/yourusername",
    },
    {
      icon: Twitter,
      label: "Twitter",
      value: "@yourhandle",
      href: "https://twitter.com/yourhandle",
    },
  ];

  return (
    <section id="contact" className="py-20 gradient-subtle">
      <div className="container mx-auto px-4">
        <div className="max-w-4xl mx-auto">
          <h2 
            ref={titleAnimation.ref}
            className={`text-4xl md:text-5xl font-bold mb-6 text-center transition-all duration-700 ${
              titleAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={titleAnimation.style}
          >
            Get In Touch
          </h2>
          <p 
            ref={descAnimation.ref}
            className={`text-lg text-muted-foreground mb-12 text-center transition-all duration-700 ${
              descAnimation.inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
            }`}
            style={descAnimation.style}
          >
            I'm always open to discussing new opportunities and collaborations
          </p>

          <Card 
            ref={cardAnimation.ref}
            className={`p-8 shadow-medium hover-lift transition-all duration-700 ${
              cardAnimation.inView ? 'opacity-100 scale-100' : 'opacity-0 scale-95'
            }`}
            style={cardAnimation.style}
          >
            <div className="grid md:grid-cols-2 gap-6">
              {contactMethods.map((method, index) => (
                <Button
                  key={index}
                  variant="outline"
                  className="h-auto p-6 flex flex-col items-start gap-2 hover:border-accent hover:shadow-soft transition-all hover:scale-105 group"
                  asChild
                >
                  <a href={method.href} target="_blank" rel="noopener noreferrer">
                    <div className="flex items-center gap-3 w-full">
                      <method.icon className="w-6 h-6 text-accent group-hover:scale-110 transition-transform" />
                      <div className="text-left">
                        <div className="font-semibold group-hover:text-accent transition-colors">{method.label}</div>
                        <div className="text-sm text-muted-foreground">{method.value}</div>
                      </div>
                    </div>
                  </a>
                </Button>
              ))}
            </div>

            <div className="mt-8 p-6 bg-secondary rounded-lg text-center">
              <p className="text-lg mb-4">
                Looking for a dedicated frontend developer?
              </p>
              <Button
                size="lg"
                className="bg-accent hover:bg-accent/90 text-accent-foreground hover:scale-105 hover:shadow-strong transition-all"
                asChild
              >
                <a href="mailto:your.email@example.com">Send Me an Email</a>
              </Button>
            </div>
          </Card>
        </div>
      </div>

      {/* Footer */}
      <div className="mt-20 text-center text-muted-foreground">
        <p>© {new Date().getFullYear()} Portfolio. Built with React & Tailwind CSS.</p>
      </div>
    </section>
  );
};

export default Contact;
