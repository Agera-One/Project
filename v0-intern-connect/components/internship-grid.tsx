import InternshipCard from "@/components/internship-card"
import { Search } from "lucide-react"
import type { Internship } from "@/lib/data"

interface InternshipGridProps {
  internships: Internship[]
}

export default function InternshipGrid({ internships }: InternshipGridProps) {
  return (
    <section className="bg-background py-12 sm:py-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-8">
          <h3 className="text-2xl font-bold text-foreground">{internships.length} Opportunities Found</h3>
          <p className="text-muted-foreground mt-2">
            {internships.length === 0
              ? "Try adjusting your filters to find more opportunities."
              : "Choose an internship that matches your career goals."}
          </p>
        </div>

        {internships.length === 0 ? (
          <div className="text-center py-12">
            <Search className="w-16 h-16 mx-auto mb-4 text-muted-foreground" />
            <h4 className="text-xl font-semibold text-foreground mb-2">No internships found</h4>
            <p className="text-muted-foreground">Try selecting different filters to discover more opportunities.</p>
          </div>
        ) : (
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {internships.map((internship) => (
              <InternshipCard key={internship.id} internship={internship} />
            ))}
          </div>
        )}
      </div>
    </section>
  )
}
