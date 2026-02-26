import {
  Avatar,
  AvatarFallback,
  AvatarBadge,
} from "@/components/ui/avatar"

interface AvatarWithBadgeProps {
  name: string
  online?: boolean
}

export function AvatarWithBadge({
  name,
  online = true,
}: AvatarWithBadgeProps) {
  const initials = name
    .trim()
    .split(" ")
    .slice(0, 2)
    .map((word) => word[0])
    .join("")
    .toUpperCase()

  return (
    <Avatar className="h-9 w-9">
      <AvatarFallback className="bg-zinc-700/50 text-gray-300 font-medium">
        {initials}
      </AvatarFallback>

      {online && (
        <AvatarBadge className="bg-green-600 dark:bg-green-800" />
      )}
    </Avatar>
  )
}
