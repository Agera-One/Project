'use client'

import { useEffect, useRef, useState } from 'react'

const timelineEvents = [
  {
    year: 2020,
    title: 'Started Web Development Journey',
    description: 'Began learning HTML, CSS, and JavaScript with a focus on creating accessible, responsive websites.',
    icon: '🚀',
  },
  {
    year: 2021,
    title: 'Mastered React Fundamentals',
    description: 'Deep-dived into React ecosystem, understanding hooks, state management, and component architecture.',
    icon: '⚛️',
  },
  {
    year: 2022,
    title: 'Specialized in Next.js & Performance',
    description: 'Focused on server-side rendering, optimization techniques, and building production-ready applications.',
    icon: '⚡',
  },
  {
    year: 2023,
    title: 'TypeScript & Advanced Patterns',
    description: 'Mastered TypeScript for type-safe development and implemented advanced design patterns.',
    icon: '🎯',
  },
  {
    year: 2024,
    title: 'Full-Stack Capabilities',
    description: 'Expanded knowledge to full-stack development, APIs, and backend integration.',
    icon: '🌐',
  },
]

export default function Timeline() {
  const [isVisible, setIsVisible] = useState(false)
  const [visibleItems, setVisibleItems] = useState<boolean[]>([])
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

  useEffect(() => {
    if (isVisible) {
      timelineEvents.forEach((_, idx) => {
        setTimeout(() => {
          setVisibleItems((prev) => {
            const newVisible = [...prev]
            newVisible[idx] = true
            return newVisible
          })
        }, idx * 100)
      })
    }
  }, [isVisible])

  return (
    <section
      id="timeline"
      ref={ref}
      className="py-20 md:py-32 px-4 md:px-6 bg-card/50"
    >
      <div className="max-w-4xl mx-auto">
        <h2 className="text-4xl md:text-5xl font-bold text-foreground mb-4">
          Skill Development Timeline
        </h2>
        <p className="text-muted-foreground text-lg mb-16">
          My continuous journey of growth and mastery in frontend development
        </p>

        <div className="space-y-8">
          {timelineEvents.map((event, idx) => (
            <div
              key={idx}
              className={`transition-all duration-700 transform ${
                visibleItems[idx]
                  ? 'opacity-100 translate-x-0'
                  : 'opacity-0 -translate-x-8'
              }`}
            >
              <div className="flex gap-6">
                {/* Timeline marker */}
                <div className="flex flex-col items-center">
                  <div className="w-12 h-12 rounded-full bg-accent text-accent-foreground flex items-center justify-center font-bold text-lg mb-4">
                    {event.icon}
                  </div>
                  {idx < timelineEvents.length - 1 && (
                    <div className="w-1 h-24 bg-gradient-to-b from-accent to-accent/30" />
                  )}
                </div>

                {/* Timeline content */}
                <div className="pb-8 pt-2 flex-1">
                  <div className="bg-background rounded-lg p-6 border border-border hover:border-accent transition-all duration-300">
                    <div className="flex items-baseline gap-4 mb-2">
                      <span className="text-2xl font-bold text-accent">{event.year}</span>
                      <h3 className="text-xl font-semibold text-foreground">
                        {event.title}
                      </h3>
                    </div>
                    <p className="text-muted-foreground leading-relaxed">
                      {event.description}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
