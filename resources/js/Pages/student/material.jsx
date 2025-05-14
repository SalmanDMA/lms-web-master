import { useMemo } from "react"
import { Link, usePage } from "@inertiajs/react"

// LAYOUT
import DefaultLayout from "@/components/layouts/default"
import HeroStudent from "@/components/ui/hero-student"

// ICONS
import { BookOpen } from "lucide-react"

// UI
import {
  Alert,
  AlertDescription,
  AlertTitle,
} from "@/components/ui/alert"

export default function StudentMaterial() {
  const { materialData, title } = usePage().props
  const listMaterial = useMemo(() => materialData, [])

  return (
    <DefaultLayout title={title} padding={false}>
      <HeroStudent
        path={"/student/material"}
        text="Selamat Datang di Materi Pembelajaran Anda!"
      />

      <div className="mx-auto grid max-w-[800px] gap-3 p-4 lg:p-6">
        {listMaterial.map((material) => (
          <Link href={`/student/material-detail/${material.id}`}>
            <Alert variant="primary">
              <BookOpen className="h-4 w-4 bg-white" />
              <AlertTitle>{material.material_title}</AlertTitle>
              <AlertDescription className="text-primary-foreground/60">
                {material.material_description}
              </AlertDescription>
            </Alert>
          </Link>
        ))}
      </div>
    </DefaultLayout>
  )
}