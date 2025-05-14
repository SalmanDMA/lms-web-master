import { usePage } from "@inertiajs/react"

// LAYOUT
import DefaultLayout from "@/components/layouts/default"
import HeroStudent from "@/components/ui/hero-student"

export default function StudentDashboard({ title }) {
  const { user } = usePage().props

  return (
    <DefaultLayout title={title} padding={false}>
      <HeroStudent user={user.fullname} />
    </DefaultLayout>
  )
}