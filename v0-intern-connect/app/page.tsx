"use client"

import { useState, useMemo } from "react"
import Header from "@/components/header"
import Hero from "@/components/hero"
import FilterSection from "@/components/filter-section"
import InternshipGrid from "@/components/internship-grid"
import { internshipData, majors } from "@/lib/data"

export default function Home() {
  const [selectedMajor, setSelectedMajor] = useState<string>("")
  const [selectedField, setSelectedField] = useState<string>("")

  // Get fields for selected major
  const fieldsForMajor = useMemo(() => {
    if (!selectedMajor) return []
    const major = majors.find((m) => m.id === selectedMajor)
    return major?.fields || []
  }, [selectedMajor])

  // Filter internships
  const filteredInternships = useMemo(() => {
    return internshipData.filter((internship) => {
      if (selectedMajor && internship.majorId !== selectedMajor) return false
      if (selectedField && internship.fieldId !== selectedField) return false
      return true
    })
  }, [selectedMajor, selectedField])

  // Reset field when major changes
  const handleMajorChange = (majorId: string) => {
    setSelectedMajor(majorId)
    setSelectedField("")
  }

  return (
    <main className="min-h-screen bg-background">
      <Header />
      <Hero />
      <FilterSection
        majors={majors}
        fields={fieldsForMajor}
        selectedMajor={selectedMajor}
        selectedField={selectedField}
        onMajorChange={handleMajorChange}
        onFieldChange={setSelectedField}
      />
      <InternshipGrid internships={filteredInternships} />
    </main>
  )
}
