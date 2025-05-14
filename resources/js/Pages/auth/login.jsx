import { useState } from "react"
import { Link, useForm } from "@inertiajs/react"

// LAYOUT
import AuthLayout from "@/components/layouts/auth"

// ICONS
import { TriangleAlert } from "lucide-react"

// UI
import {
  Alert,
  AlertDescription,
  AlertTitle,
} from "@/components/ui/alert"
import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import { Input } from "@/components/ui/input"

export default function Login({ role, title }) {
  const { data, setData, post, processing, errors } = useForm({
    email: "",
    password: "",
    nisn: "",
    nip: "",
  })
  const [passwordVisibility, setPasswordVisibility] = useState(false)
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/

  function submit(e) {
    e.preventDefault()
    post(`/login/${role}`)
  }

  return (
    <AuthLayout role={role} title={title}>
      <div className="grid gap-2 text-center">
        <h1 className="text-3xl font-bold text-primary">Selamat Datang,</h1>
        <p className="text-balance text-muted-foreground">
          Masuk ke akun Anda terlebih dahulu
        </p>
      </div>
      <form onSubmit={submit}>
        <div className="grid gap-4">
          {errors.email && (
            <Alert
              className="cursor-pointer"
              variant="destructive"
            >
              <TriangleAlert className="h-4 w-4" />
              <AlertTitle>Login Gagal!</AlertTitle>
              <AlertDescription>
                {errors.email}
              </AlertDescription>
            </Alert>
          )}
          <Input
            type="text"
            placeholder={`Masukkan ${role === "teacher" ? "NIP" : "NISN"} atau email Anda`}
            onChange={e => {
              if (emailRegex.test(e.target.value)) {
                setData('email', e.target.value)
              } else {
                role === "teacher" ?
                  setData('nip', e.target.value) :
                  setData('nisn', e.target.value)
              }
            }}
            required
          />
          <Input
            type={passwordVisibility ? "text" : "password"}
            placeholder="Masukkan kata sandi Anda"
            value={data.password}
            onChange={e => setData('password', e.target.value)}
            required
          />
          <div className="flex items-center space-x-2">
            <Checkbox
              id="password-visibility"
              checked={passwordVisibility}
              onCheckedChange={setPasswordVisibility}
            />
            <label
              htmlFor="password-visibility"
              className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
            >
              Tampilkan kata sandi
            </label>
          </div>
          <div className="text-center text-sm">
            Kesulitan masuk? Reset kata sandi{" "}
            <Link href={`/forgot-password${role === "teacher" ? "/teacher" : ""}`} className="text-primary underline">
              di sini!
            </Link>
          </div>
          <Button type="submit" className="w-full" disabled={processing}>
            Masuk
          </Button>
        </div>
      </form>
      <div className="mt-4 text-center text-sm">
        Belum punya akun?{" "}
        <Link href={`/register${role === "teacher" ? "/teacher" : ""}`} className="text-primary underline">
          Daftar
        </Link>
        {" "} dulu yuk!
      </div>
    </AuthLayout>
  )
}
