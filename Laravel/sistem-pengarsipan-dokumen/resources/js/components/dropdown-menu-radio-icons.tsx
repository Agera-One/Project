"use client"

import * as React from "react"
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import {
  CalendarArrowUp,
  CalendarArrowDown,
  AArrowDown,
  AArrowUp,
  Check,
} from "lucide-react"
import { cn } from "@/lib/utils"

export function DropdownMenuRadioIcons() {
  const [sort, setSort] = React.useState("az")

  const items = [
    {
      value: "az",
      label: "Sort by name (A–Z)",
      icon: AArrowUp,
    },
    {
      value: "za",
      label: "Sort by name (Z–A)",
      icon: AArrowDown,
    },
    {
      value: "newest",
      label: "Sort by date (newest first)",
      icon: CalendarArrowUp,
    },
    {
      value: "oldest",
      label: "Sort by date (oldest first)",
      icon: CalendarArrowDown,
    },
  ]

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="outline">Sort</Button>
      </DropdownMenuTrigger>

      <DropdownMenuContent className="w-64 bg-sidebar">
        <DropdownMenuGroup>
          {items.map((item) => {
            const Icon = item.icon
            const isSelected = sort === item.value

            return (
              <DropdownMenuItem
                key={item.value}
                onSelect={() => setSort(item.value)}
                className="flex items-center justify-between"
              >
                <div className="flex items-center">
                  <Icon className="mr-2 h-4 w-4" />
                  <span>{item.label}</span>
                </div>

                <Check
                  className={cn(
                    "h-4 w-4",
                    isSelected ? "opacity-100" : "opacity-0"
                  )}
                />
              </DropdownMenuItem>
            )
          })}
        </DropdownMenuGroup>
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
