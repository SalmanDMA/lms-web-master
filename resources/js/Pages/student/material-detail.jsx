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

export default function StudentMaterialDetail() {
  const { materialData, title } = usePage().props
  const material = useMemo(() => materialData, [])

  return (
    <DefaultLayout title={title} padding={false}>
      <HeroStudent
        path={"/student/material"}
        text="Selamat Datang di Materi Pembelajaran Anda!"
      />

      <div className="mx-auto grid max-w-[800px] justify-center gap-3 p-4 lg:p-6">
        <h1 className="text-center text-3xl font-bold text-primary">{material.material_title}</h1>
        <h3 className="text-center text-xs text-primary">Diterbitkan pada {material.shared_at}</h3>
        <p className="mb-3">{material.material_description}</p>
        <h2 className="text-center text-xl font-bold text-primary">Materi Tambahan</h2>
        <div className="grid gap-3">
          {material.material_resources.map((resource) => (
            <Link href={resource.resource_url}>
              <Alert>
                <BookOpen className="h-4 w-4 bg-white" />
                <AlertTitle>
                  <div>{resource.resource_name}</div>
                  <div className="text-xs text-muted-foreground">
                    {resource.resource_type} • {resource.resource_size}
                  </div>
                </AlertTitle>
                <AlertDescription className="text-primary-foreground/60">
                  {resource.resource_description}
                </AlertDescription>
              </Alert>
            </Link>
          ))}
        </div>
      </div>
    </DefaultLayout>
  )
}