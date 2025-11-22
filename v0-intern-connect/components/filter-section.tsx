"use client"

import { Button } from "@/components/ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import type { Major, Field } from "@/lib/data"

interface FilterSectionProps {
  majors: Major[]
  fields: Field[]
  selectedMajor: string
  selectedField: string
  onMajorChange: (majorId: string) => void
  onFieldChange: (fieldId: string) => void
}

export default function FilterSection({
  majors,
  fields,
  selectedMajor,
  selectedField,
  onMajorChange,
  onFieldChange,
}: FilterSectionProps) {
  const handleClearFilters = () => {
    onMajorChange("")
    onFieldChange("")
  }

  return (
    <section className="bg-background border-b border-border">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h3 className="text-lg font-semibold text-foreground mb-6">Filter by Program</h3>
        <div className="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
          <div className="w-full sm:w-64">
            <label className="block text-sm font-medium text-foreground mb-2">Major Program</label>
            <Select value={selectedMajor} onValueChange={onMajorChange}>
              <SelectTrigger className="w-full">
                <SelectValue placeholder="Select a major..." />
              </SelectTrigger>
              <SelectContent>
                {majors.map((major) => (
                  <SelectItem key={major.id} value={major.id}>
                    {major.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          {fields.length > 0 && (
            <div className="w-full sm:w-64">
              <label className="block text-sm font-medium text-foreground mb-2">Specialization</label>
              <Select value={selectedField} onValueChange={onFieldChange}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select a field..." />
                </SelectTrigger>
                <SelectContent>
                  {fields.map((field) => (
                    <SelectItem key={field.id} value={field.id}>
                      {field.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          )}

          {(selectedMajor || selectedField) && (
            <Button onClick={handleClearFilters} variant="outline" className="w-full sm:w-auto bg-transparent">
              Clear Filters
            </Button>
          )}
        </div>
      </div>
    </section>
  )
}
