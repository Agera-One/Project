'use client'

import { useEffect, useRef, useState } from 'react'

export default function About() {
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
      id="about"
      ref={ref}
      className="py-20 md:py-32 px-4 md:px-6 bg-card/50"
    >
      <div className="max-w-4xl mx-auto">
        <div
          className={`transition-all duration-1000 transform ${
            isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
          }`}
        >
          <h2 className="text-4xl md:text-5xl font-bold text-foreground mb-8">
            About Me
          </h2>

          <div className="grid md:grid-cols-2 gap-12">
            <div className="space-y-6">
              <p className="text-lg text-muted-foreground leading-relaxed">
                I'm a passionate frontend developer focused on creating pixel-perfect, accessible web experiences that solve real problems. With a foundation in both design and engineering, I bridge the gap between beautiful interfaces and robust functionality.
              </p>

              <p className="text-lg text-muted-foreground leading-relaxed">
                My approach combines modern development practices with user-centered design thinking. I believe great frontend work requires attention to performance, accessibility, and user experience—not just aesthetics.
              </p>

              <p className="text-lg text-muted-foreground leading-relaxed">
                When I'm not coding, you'll find me exploring new web technologies, contributing to open source, or sharing knowledge with the developer community.
              </p>
            </div>

            <div className="bg-background rounded-xl p-8 border border-border">
              <h3 className="text-xl font-semibold text-foreground mb-6">Quick Facts</h3>
              <ul className="space-y-4">
                <li className="flex items-start gap-3">
                  <span className="text-accent font-bold">→</span>
                  <span className="text-muted-foreground"><strong className="text-foreground">Specialization:</strong> React, Next.js, TypeScript</span>
                </li>
                <li className="flex items-start gap-3">
                  <span className="text-accent font-bold">→</span>
                  <span className="text-muted-foreground"><strong className="text-foreground">Focus:</strong> Web Performance & Accessibility</span>
                </li>
                <li className="flex items-start gap-3">
                  <span className="text-accent font-bold">→</span>
                  <span className="text-muted-foreground"><strong className="text-foreground">Years Active:</strong> 5+ years</span>
                </li>
                <li className="flex items-start gap-3">
                  <span className="text-accent font-bold">→</span>
                  <span className="text-muted-foreground"><strong className="text-foreground">Current Goal:</strong> Building scalable, maintainable frontends</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
