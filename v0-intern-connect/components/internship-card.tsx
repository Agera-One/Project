"use client"

import { MapPin, Briefcase, BookOpen } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import type { Internship } from "@/lib/data"

interface InternshipCardProps {
  internship: Internship
}

export default function InternshipCard({ internship }: InternshipCardProps) {
  return (
    <Card className="flex flex-col h-full hover:shadow-lg transition-shadow">
      <CardHeader>
        <div className="flex items-start justify-between gap-4 mb-2">
          <div>
            <CardTitle className="text-xl">{internship.company}</CardTitle>
            <CardDescription>{internship.field}</CardDescription>
          </div>
          <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900 dark:to-indigo-900 flex items-center justify-center flex-shrink-0">
            <Briefcase className="w-5 h-5 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
      </CardHeader>

      <CardContent className="flex-1 flex flex-col gap-4">
        <p className="text-sm text-muted-foreground leading-relaxed">{internship.description}</p>

        <div className="space-y-3 text-sm">
          <div className="flex items-center gap-2 text-muted-foreground">
            <BookOpen className="w-4 h-4 flex-shrink-0" />
            <span>{internship.major}</span>
          </div>
          <div className="flex items-center gap-2 text-muted-foreground">
            <MapPin className="w-4 h-4 flex-shrink-0" />
            <span>{internship.location}</span>
          </div>
        </div>

        <div className="flex flex-wrap gap-2 pt-2">
          <span className="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">
            {internship.duration}
          </span>
          <span className="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">
            {internship.type}
          </span>
        </div>

        <Button className="w-full mt-auto bg-blue-600 hover:bg-blue-700">View Details</Button>
      </CardContent>
    </Card>
  )
}
