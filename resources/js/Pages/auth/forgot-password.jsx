import { Link, useForm } from "@inertiajs/react"
import forgotPassImage from "@/assets/img/forgot-password.png"

// Layout
import AuthLayout from "@/components/layouts/auth"

// UI
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"

export default function ForgotPassword({ role, title }) {
  return (
    <AuthLayout role={role} title={title}>
      <div className="flex flex-col items-center gap-2 text-center">
        <img src={forgotPassImage} alt="Lock" width="150px" />
        <h1 className="text-3xl font-bold text-primary">Lupa Kata Sandi</h1>
        <p className="text-balance text-muted-foreground">
          Masukkan email Anda untuk mendapatkan<br />kode verifikasi
        </p>
      </div>
      <div className="grid gap-4">
        <Input
          id="email"
          type="email"
          placeholder="Email"
          required
        />
        <Link href={`/login${role === "guru" ? "/guru" : ""}`} className="text-center text-sm text-primary underline">
          Coba opsi lain
        </Link>
        <Button type="submit" className="w-full">
          Kirim
        </Button>
      </div>
    </AuthLayout>
  )
}
