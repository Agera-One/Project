'use client'

import { useEffect, useState } from 'react'
import { ArrowDown } from 'lucide-react'

export default function Hero() {
  const [isVisible, setIsVisible] = useState(false)

  useEffect(() => {
    setIsVisible(true)
  }, [])

  const scrollToAbout = () => {
    const element = document.getElementById('about')
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' })
    }
  }

  return (
    <section className="min-h-screen flex items-center justify-center relative pt-20 px-4 md:px-6 bg-gradient-to-br from-background via-background to-muted/20">
      <div className="max-w-4xl w-full">
        <div
          className={`transition-all duration-1000 transform ${
            isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
          }`}
        >
          <h1 className="text-5xl md:text-7xl font-bold text-foreground mb-6 leading-tight">
            Frontend <span className="text-accent">Developer</span> & Creative Technologist
          </h1>
          <p className="text-lg md:text-xl text-muted-foreground mb-8 max-w-2xl leading-relaxed">
            Crafting modern web experiences with clean code, intuitive design, and a passion for performance. I build interfaces that users love and developers understand.
          </p>

          <div className="flex flex-col sm:flex-row gap-4 mb-16">
            <button
              onClick={scrollToAbout}
              className="px-8 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-accent transition-all duration-300 hover:shadow-lg"
            >
              Explore My Work
            </button>
            <a
              href="#contact"
              className="px-8 py-3 border border-accent text-accent font-medium rounded-lg hover:bg-accent/10 transition-all duration-300"
            >
              Get In Touch
            </a>
          </div>
        </div>

        {/* Scroll Indicator */}
        <div className="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
          <button
            onClick={scrollToAbout}
            className="flex flex-col items-center gap-2 text-muted-foreground hover:text-accent transition-colors"
          >
            <span className="text-sm font-medium">Scroll to explore</span>
            <ArrowDown size={20} />
          </button>
        </div>
      </div>
    </section>
  )
}
