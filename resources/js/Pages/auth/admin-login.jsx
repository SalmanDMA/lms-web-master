import { useState } from "react"
import { Head, useForm } from "@inertiajs/react"

// ICONS
import { TriangleAlert } from "lucide-react"

// UI
import {
  Alert,
  AlertDescription,
  AlertTitle,
} from "@/components/ui/alert"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardTitle,
} from "@/components/ui/card"
import { Checkbox } from "@/components/ui/checkbox"
import { Input } from "@/components/ui/input"

export default function AdminLogin({ title }) {
  const { data, setData, post, processing, errors } = useForm({
    email: "",
    password: "",
  })
  const [passwordVisibility, setPasswordVisibility] = useState(false)

  function submit(e) {
    e.preventDefault()
    post("/admin/login")
  }

  return (
    <>
      <Head title={`${title} - TMB Learning Management System`} />
      <div className="flex min-h-screen items-center justify-center py-12">
        <Card className="mx-auto grid min-h-full w-[450px] gap-6 p-4 pt-12">
          <CardTitle className="grid gap-2 text-center">
            <p className="text-3xl font-bold text-primary">Selamat Datang,</p>
            <p className="text-balance text-xl text-muted-foreground">
              Masuk ke akun Anda terlebih dahulu
            </p>
          </CardTitle>
          <CardContent>
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
                  id="email"
                  type="email"
                  placeholder={`Masukkan email Anda`}
                  value={data.email}
                  onChange={e => setData('email', e.target.value)}
                  required
                />
                <Input
                  id="password"
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
                <Button type="submit" className="w-full" disabled={processing}>
                  Masuk
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </>
  )
}
