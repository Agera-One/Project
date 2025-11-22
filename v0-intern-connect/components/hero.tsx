export default function Hero() {
  return (
    <section className="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-900 dark:to-slate-800 py-16 sm:py-24">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 className="text-4xl sm:text-5xl font-bold text-foreground mb-4 text-balance">Launch Your Career Journey</h2>
        <p className="text-lg text-muted-foreground mb-8 max-w-2xl mx-auto text-pretty">
          Discover internship opportunities across four major programs. Connect with top companies and gain real-world
          experience.
        </p>
        <div className="flex gap-4 justify-center flex-wrap">
          <button className="px-6 py-3 rounded-full bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors">
            Explore Internships
          </button>
          <button className="px-6 py-3 rounded-full bg-white text-blue-600 font-medium border border-border hover:bg-gray-50 transition-colors dark:bg-slate-800 dark:text-blue-400">
            Learn More
          </button>
        </div>
      </div>
    </section>
  )
}
