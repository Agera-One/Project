"use client"

export default function Header() {
  return (
    <header className="sticky top-0 z-50 bg-background border-b border-border">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
        <div className="flex items-center gap-2">
          <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
            <span className="text-white font-bold text-lg">IC</span>
          </div>
          <div>
            <h1 className="text-xl font-bold text-foreground">InternConnect</h1>
            <p className="text-xs text-muted-foreground">SMK Opportunities Hub</p>
          </div>
        </div>
        <nav className="flex items-center gap-6">
          <button className="text-sm text-muted-foreground hover:text-foreground transition-colors">Browse</button>
          <button className="text-sm text-muted-foreground hover:text-foreground transition-colors">About</button>
          <button className="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition-colors">
            Sign In
          </button>
        </nav>
      </div>
    </header>
  )
}
