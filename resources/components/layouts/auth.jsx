import { Head } from "@inertiajs/react"
import authStudentImage from "@/assets/img/auth-siswa.png"
import authTeacherImage from "@/assets/img/auth-guru.png"

export default function AuthLayout({ children, role, title }) {
  return (
    <div className="w-full lg:grid lg:min-h-[600px] lg:grid-cols-2 xl:min-h-screen">
      <Head title={`${title} - TMB Learning Management System`} />
      <div className="flex items-center justify-center py-12">
        <div className="mx-auto grid w-[450px] gap-6 px-8">
          {children}
        </div>
      </div>
      <div className="hidden bg-muted lg:block">
        <img
          src={role === "teacher" ? authTeacherImage : authStudentImage}
          alt="Image"
          width="1920"
          height="1080"
          className="h-full w-full object-cover dark:brightness-[0.2] dark:grayscale"
        />
      </div>
    </div>
  )
}