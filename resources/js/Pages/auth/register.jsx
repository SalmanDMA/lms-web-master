import { Link, useForm } from "@inertiajs/react"

// Layout
import AuthLayout from "@/components/layouts/auth"

// UI
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"

export default function Register({ role, title }) {
  return (
    <AuthLayout role={role} title={title}>
      <div className="grid gap-2 text-center">
        <h1 className="text-3xl font-bold text-primary">Selamat Datang,</h1>
        <p className="text-balance text-muted-foreground">
          Isi formulir di bawah ini untuk membuat<br />akunmu, ya!
        </p>
      </div>
      <div className="grid gap-4">
        <Input
          id="nama"
          type="nama"
          placeholder="Nama Lengkap"
          required
        />
        {role === "siswa" && (
          <Input
            id="nisn"
            type="nisn"
            placeholder="NISN"
            required
          />
        )}
        {role === "guru" && (
          <Input
            id="nip"
            type="nip"
            placeholder="NIP"
            required
          />
        )}
        {role === "siswa" && (
          <div className="grid grid-cols-2 gap-2">
            <Input
              id="kelas"
              type="kelas"
              placeholder="Kelas"
              required
            />
            <Input
              id="sub_kelas"
              type="sub_kelas"
              placeholder="Sub Kelas"
              required
            />
          </div>
        )}
        <Input
          id="email"
          type="email"
          placeholder="Email"
          required
        />
        <Input
          id="password"
          type="password"
          placeholder="Kata sandi"
          required
        />
        <Input
          id="confirm_password"
          type="confirm_password"
          placeholder="Tulis ulang kata sandi"
          required
        />
        <Button type="submit" className="w-full">
          Daftar
        </Button>
      </div>
      <div className="mt-4 text-center text-sm">
        Sudah punya akun? Yuk,{" "}
        <Link href={`/login${role === "guru" ? "/guru" : ""}`} className="text-primary underline">
          Masuk
        </Link>
        {" "}di sini!
      </div>
    </AuthLayout>
  )
}
