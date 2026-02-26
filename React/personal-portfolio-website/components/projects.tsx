'use client'

import { useEffect, useRef, useState } from 'react'
import { ExternalLink, Github } from 'lucide-react'

const projects = [
  {
    title: 'E-Commerce Platform',
    description: 'Full-featured e-commerce solution with product catalog, cart management, and secure checkout flow.',
    tags: ['React', 'Next.js', 'TypeScript', 'Tailwind CSS', 'Stripe'],
    link: '#',
    github: '#',
    color: 'from-blue-500/20 to-cyan-500/20',
  },
  {
    title: 'Design System Library',
    description: 'Comprehensive UI component library with documentation, accessibility features, and Storybook integration.',
    tags: ['React', 'TypeScript', 'Storybook', 'Tailwind CSS'],
    link: '#',
    github: '#',
    color: 'from-purple-500/20 to-pink-500/20',
  },
  {
    title: 'Real-time Analytics Dashboard',
    description: 'Interactive dashboard with real-time data visualization, charts, and performance metrics tracking.',
    tags: ['React', 'WebSocket', 'Recharts', 'TypeScript', 'Vercel'],
    link: '#',
    github: '#',
    color: 'from-green-500/20 to-emerald-500/20',
  },
  {
    title: 'Content Management System',
    description: 'Headless CMS with rich text editing, media management, and multi-language support.',
    tags: ['Next.js', 'TypeScript', 'Markdown', 'MDX', 'MDX'],
    link: '#',
    github: '#',
    color: 'from-orange-500/20 to-red-500/20',
  },
]

export default function Projects() {
  const [isVisible, setIsVisible] = useState(false)
  const ref = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsVisible(true)
        }
      },
      { threshold: 0.2 }
    )

    if (ref.current) {
      observer.observe(ref.current)
    }

    return () => observer.disconnect()
  }, [])

  return (
    <section
      id="projects"
      ref={ref}
      className="py-20 md:py-32 px-4 md:px-6 bg-background"
    >
      <div className="max-w-5xl mx-auto">
        <div
          className={`transition-all duration-1000 transform ${
            isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
          }`}
        >
          <h2 className="text-4xl md:text-5xl font-bold text-foreground mb-4">
            Featured Projects
          </h2>
          <p className="text-muted-foreground text-lg mb-12">
            Showcase of selected projects demonstrating technical expertise and problem-solving
          </p>

          <div className="grid md:grid-cols-2 gap-8">
            {projects.map((project, idx) => (
              <div
                key={idx}
                className={`transition-all duration-1000 transform ${
                  isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
                }`}
                style={{
                  transitionDelay: `${idx * 100}ms`,
                }}
              >
                <div className={`h-40 rounded-t-xl bg-gradient-to-br ${project.color} border border-border border-b-0`} />

                <div className="bg-card rounded-b-xl p-6 border border-border border-t-0 hover:border-accent transition-all duration-300 group">
                  <h3 className="text-xl font-semibold text-foreground mb-3 group-hover:text-accent transition-colors">
                    {project.title}
                  </h3>

                  <p className="text-muted-foreground text-sm leading-relaxed mb-4">
                    {project.description}
                  </p>

                  <div className="flex flex-wrap gap-2 mb-6">
                    {project.tags.map((tag, i) => (
                      <span
                        key={i}
                        className="px-3 py-1 text-xs font-medium bg-background text-accent rounded-full border border-border"
                      >
                        {tag}
                      </span>
                    ))}
                  </div>

                  <div className="flex gap-3">
                    <a
                      href={project.link}
                      className="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded hover:bg-accent transition-colors"
                    >
                      View Project
                      <ExternalLink size={16} />
                    </a>
                    <a
                      href={project.github}
                      className="flex items-center gap-2 px-4 py-2 border border-border text-foreground text-sm font-medium rounded hover:bg-background/50 transition-colors"
                    >
                      Code
                      <Github size={16} />
                    </a>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}
