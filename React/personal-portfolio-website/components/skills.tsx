'use client'

import { useEffect, useRef, useState } from 'react'

const skillCategories = [
  {
    category: 'Frontend',
    skills: ['React', 'Next.js', 'TypeScript', 'Tailwind CSS', 'Vue.js', 'JavaScript'],
  },
  {
    category: 'Tools & Platforms',
    skills: ['Git', 'Vercel', 'Figma', 'Webpack', 'Vite', 'Docker'],
  },
  {
    category: 'Principles',
    skills: ['Web Accessibility', 'Performance Optimization', 'Responsive Design', 'SEO', 'Testing', 'Clean Code'],
  },
]

export default function Skills() {
  const [isVisible, setIsVisible] = useState(false)
  const ref = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsVisible(true)
        }
      },
      { threshold: 0.3 }
    )

    if (ref.current) {
      observer.observe(ref.current)
    }

    return () => observer.disconnect()
  }, [])

  return (
    <section
      id="skills"
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
            Technical Skills
          </h2>
          <p className="text-muted-foreground text-lg mb-12">
            A comprehensive overview of my technical expertise and development toolkit
          </p>

          <div className="grid md:grid-cols-3 gap-8">
            {skillCategories.map((category, idx) => (
              <div
                key={idx}
                className="group p-8 rounded-xl bg-card border border-border hover:border-accent transition-all duration-300 hover:shadow-lg"
                style={{
                  animationDelay: `${idx * 100}ms`,
                }}
              >
                <h3 className="text-xl font-semibold text-foreground mb-6">
                  {category.category}
                </h3>
                <div className="flex flex-wrap gap-3">
                  {category.skills.map((skill, i) => (
                    <span
                      key={i}
                      className="px-4 py-2 bg-background text-accent text-sm font-medium rounded-full border border-border group-hover:bg-accent/10 transition-all duration-300"
                    >
                      {skill}
                    </span>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}
