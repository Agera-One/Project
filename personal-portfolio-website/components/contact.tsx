'use client'

import { useEffect, useRef, useState } from 'react'
import { Mail, Linkedin, Github, Twitter } from 'lucide-react'

export default function Contact() {
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
      id="contact"
      ref={ref}
      className="py-20 md:py-32 px-4 md:px-6 bg-card/50"
    >
      <div className="max-w-4xl mx-auto">
        <div
          className={`transition-all duration-1000 transform text-center ${
            isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
          }`}
        >
          <h2 className="text-4xl md:text-5xl font-bold text-foreground mb-6">
            Let's Work Together
          </h2>

          <p className="text-lg text-muted-foreground max-w-2xl mx-auto mb-12 leading-relaxed">
            I'm always interested in hearing about new projects and opportunities. Whether you have a question or just want to say hello, feel free to get in touch!
          </p>

          {/* Email CTA */}
          <div className="mb-12">
            <a
              href="mailto:hello@example.com"
              className="inline-flex items-center gap-3 px-8 py-4 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-accent transition-all duration-300 hover:shadow-lg text-lg"
            >
              <Mail size={20} />
              Send me an email
            </a>
          </div>

          {/* Social Links */}
          <div className="flex justify-center gap-6 flex-wrap">
            <a
              href="#"
              className="p-3 rounded-lg bg-background border border-border text-foreground hover:text-accent hover:border-accent transition-all duration-300 hover:shadow-md"
              aria-label="GitHub"
            >
              <Github size={24} />
            </a>
            <a
              href="#"
              className="p-3 rounded-lg bg-background border border-border text-foreground hover:text-accent hover:border-accent transition-all duration-300 hover:shadow-md"
              aria-label="LinkedIn"
            >
              <Linkedin size={24} />
            </a>
            <a
              href="#"
              className="p-3 rounded-lg bg-background border border-border text-foreground hover:text-accent hover:border-accent transition-all duration-300 hover:shadow-md"
              aria-label="Twitter"
            >
              <Twitter size={24} />
            </a>
          </div>

          <p className="text-muted-foreground text-sm mt-12">
            Looking forward to connecting with you!
          </p>
        </div>
      </div>
    </section>
  )
}
