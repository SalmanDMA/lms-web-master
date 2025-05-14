import { useEffect, useMemo, useState } from "react"
import { useForm, usePage } from "@inertiajs/react"

// LAYOUT
import DefaultLayout from "@/components/layouts/default"

// ICONS
import { ChevronDown, ChevronUp, ChevronsUpDown, PencilLine, Trash2 } from "lucide-react"

// UI
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@/components/ui/alert-dialog"
import { Button } from "@/components/ui/button"
import { CardTitle } from "@/components/ui/card"
import DataTable from "@/components/ui/data-table"
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { Textarea } from "@/components/ui/textarea"
import { useToast } from "@/components/ui/use-toast"

export default function SchoolUserStudent() {
  const userStudentColumns = [
    {
      accessorKey: "id",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            ID User
            {column.getIsSorted() === "asc" ? (
              <ChevronUp className="ml-2 h-4 w-4" />
            ) : (
              column.getIsSorted() === "desc" ? (
                <ChevronDown className="ml-2 h-4 w-4" />
              ) : (
                <ChevronsUpDown className="ml-2 h-4 w-4" />
              )
            )}
          </Button>
        )
      },
    },
    {
      accessorKey: "is_student.nisn",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            NISN
            {column.getIsSorted() === "asc" ? (
              <ChevronUp className="ml-2 h-4 w-4" />
            ) : (
              column.getIsSorted() === "desc" ? (
                <ChevronDown className="ml-2 h-4 w-4" />
              ) : (
                <ChevronsUpDown className="ml-2 h-4 w-4" />
              )
            )}
          </Button>
        )
      },
    },
    {
      accessorKey: "fullname",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Nama
            {column.getIsSorted() === "asc" ? (
              <ChevronUp className="ml-2 h-4 w-4" />
            ) : (
              column.getIsSorted() === "desc" ? (
                <ChevronDown className="ml-2 h-4 w-4" />
              ) : (
                <ChevronsUpDown className="ml-2 h-4 w-4" />
              )
            )}
          </Button>
        )
      },
    },
    {
      accessorKey: "email",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Email
            {column.getIsSorted() === "asc" ? (
              <ChevronUp className="ml-2 h-4 w-4" />
            ) : (
              column.getIsSorted() === "desc" ? (
                <ChevronDown className="ml-2 h-4 w-4" />
              ) : (
                <ChevronsUpDown className="ml-2 h-4 w-4" />
              )
            )}
          </Button>
        )
      },
    },
    {
      accessorKey: "status",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Status
            {column.getIsSorted() === "asc" ? (
              <ChevronUp className="ml-2 h-4 w-4" />
            ) : (
              column.getIsSorted() === "desc" ? (
                <ChevronDown className="ml-2 h-4 w-4" />
              ) : (
                <ChevronsUpDown className="ml-2 h-4 w-4" />
              )
            )}
          </Button>
        )
      },
    },
    {
      id: "actions",
      cell: ({ row }) => {
        const [dialogUpdate, setDialogUpdate] = useState(false)
        const { data, setData, put, delete: destroy, processing, errors, reset } = useForm({
          id: "", fullname: "", nisn: "", email: "", phone: "", gender: "", religion: "",
          address: "", role: "", password: "", password_confirmation: "",
          sub_class_id: "", major: "", type: "", year: "",
        })

        function updateUserStudentSubmit(e) {
          e.preventDefault()
          put("/admin/user", {
            onSuccess: () => {
              setDialogUpdate(false)
              reset()
              toast({
                title: "Ubah Data Berhasil!",
                description: "Berhasil mengubah data user",
              })
            }
          })
        }

        function deleteUserStudentSubmit(e) {
          e.preventDefault()
          destroy("/admin/user", {
            onSuccess: () => {
              toast({
                title: "Hapus Data Berhasil!",
                description: "Berhasil menghapus data user",
              })
            }
          })
        }

        useEffect(() => {
          if (errors.message) {
            toast({
              title: errors.title,
              description: errors.message,
              variant: "destructive",
            })
          }
        }, [errors.message])

        return (
          <div className="flex items-center justify-end space-x-2">
            <Dialog open={dialogUpdate} onOpenChange={setDialogUpdate}>
              <DialogTrigger asChild>
                <Button
                  className="text-yellow-500 hover:text-yellow-500"
                  variant="outline"
                  size="icon"
                  onClick={() => setData({
                    id: row.original.id,
                    fullname: row.original.fullname,
                    nisn: row.original.is_student.nisn,
                    email: row.original.email,
                    phone: row.original.phone,
                    gender: row.original.gender,
                    religion: row.original.religion,
                    address: row.original.address,
                    role: row.original.role,
                    sub_class_id: row.original.is_student.sub_class_id,
                    major: row.original.is_student.major,
                    type: row.original.is_student.type,
                    year: row.original.is_student.year,
                  })}
                >
                  <PencilLine className="h-4 w-4" />
                </Button>
              </DialogTrigger>
              <DialogContent className="sm:max-w-[600px]">
                <form onSubmit={updateUserStudentSubmit}>
                  <DialogHeader>
                    <DialogTitle>Ubah User Siswa</DialogTitle>
                  </DialogHeader>
                  <div className="grid gap-4 py-4">
                    <div className="grid grid-cols-5 gap-4">
                      <div className="col-span-3 flex flex-col gap-2">
                        <Label htmlFor="fullname">
                          Nama Lengkap <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="fullname"
                          type="text"
                          placeholder="Nama Lengkap"
                          defaultValue={row.original.fullname}
                          onChange={e => setData("fullname", e.target.value)}
                          required
                        />
                      </div>
                      <div className="col-span-2 flex flex-col gap-2">
                        <Label htmlFor="nisn">
                          NISN <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="nisn"
                          type="text"
                          placeholder="NISN"
                          defaultValue={row.original.is_student.nisn}
                          onChange={e => setData("nisn", e.target.value)}
                          required
                        />
                      </div>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="gender">
                          Jenis Kelamin <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="gender"
                          defaultValue={row.original.gender}
                          onValueChange={value => setData("gender", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Jenis Kelamin" />
                          </SelectTrigger>
                          <SelectContent>
                            {
                              ["Laki-laki", "Perempuan"].map((gender) => (
                                <SelectItem
                                  key={gender}
                                  value={gender}
                                >
                                  {gender}
                                </SelectItem>
                              ))
                            }
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="religion">
                          Agama <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="religion"
                          defaultValue={row.original.religion}
                          onValueChange={value => setData("religion", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Agama" />
                          </SelectTrigger>
                          <SelectContent>
                            {
                              [
                                "Islam", "Kristen", "Katolik",
                                "Hindu", "Budha", "Konghucu",
                              ].map((religion) => (
                                <SelectItem
                                  key={religion}
                                  value={religion}
                                >
                                  {religion}
                                </SelectItem>
                              ))
                            }
                          </SelectContent>
                        </Select>
                      </div>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="address">
                        Alamat <span className="text-red-500">*</span>
                      </Label>
                      <Textarea
                        id="address"
                        placeholder="Alamat"
                        defaultValue={row.original.address}
                        onChange={e => setData("address", e.target.value)}
                        required
                      />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="email">
                          Email <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="email"
                          type="email"
                          placeholder="Email"
                          defaultValue={row.original.email}
                          onChange={e => setData("email", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="phone">
                          Telepon <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="phone"
                          type="text"
                          placeholder="Telepon"
                          defaultValue={row.original.phone}
                          onChange={e => setData("phone", e.target.value)}
                          required
                        />
                      </div>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="password">
                          Password
                        </Label>
                        <Input
                          id="password"
                          type="text"
                          placeholder="Password"
                          defaultValue={row.original.password}
                          onChange={e => setData("password", e.target.value)}
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="password_confirmation">
                          Konfirmasi Password
                        </Label>
                        <Input
                          id="password_confirmation"
                          type="text"
                          placeholder="Konfirmasi Password"
                          defaultValue={row.original.password_confirmation}
                          onChange={e => setData("password_confirmation", e.target.value)}
                        />
                      </div>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="sub_class_id">
                        Kelas <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="sub_class_id"
                        defaultValue={row.original.is_student.sub_class_id}
                        onValueChange={value => setData("sub_class_id", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Kelas" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            listSubClass.map((kelas) => (
                              <SelectItem
                                key={kelas.id}
                                value={kelas.id}
                              >
                                {`${kelas.id} - ${kelas.name}`}
                              </SelectItem>
                            ))
                          }
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="major">
                        Jurusan <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="major"
                        defaultValue={row.original.is_student.major}
                        onValueChange={value => setData("major", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Jurusan" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            listMajor.map((major) => (
                              <SelectItem
                                key={major.id}
                                value={major.id}
                              >
                                {`${major.id} - ${major.name}`}
                              </SelectItem>
                            ))
                          }
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="type">
                          Tipe <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="type"
                          defaultValue={row.original.is_student.type}
                          onValueChange={value => setData("type", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Tipe" />
                          </SelectTrigger>
                          <SelectContent>
                            {
                              ["Umum", "SKTM", "Prestasi"].map((type) => (
                                <SelectItem
                                  key={type}
                                  value={type}
                                >
                                  {type}
                                </SelectItem>
                              ))
                            }
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="year">
                          Tahun <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="year"
                          type="text"
                          placeholder="Tahun"
                          defaultValue={row.original.is_student.year}
                          onChange={e => setData("year", e.target.value)}
                          required
                        />
                      </div>
                    </div>
                  </div>
                  <DialogFooter>
                    <Button type="submit" disabled={processing}>
                      Ubah
                    </Button>
                  </DialogFooter>
                </form>
              </DialogContent>
            </Dialog>
            <AlertDialog>
              <AlertDialogTrigger asChild>
                <Button className="text-red-500 hover:text-red-500" variant="outline" size="icon">
                  <Trash2 className="h-4 w-4" />
                </Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>Anda Yakin?</AlertDialogTitle>
                  <AlertDialogDescription>
                    Data yang telah dihapus tidak dapat dikembalikan.
                    Pastikan data yang dipilih telah benar.
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>Batal</AlertDialogCancel>
                  <form onSubmit={deleteUserStudentSubmit}>
                    <input
                      type="hidden"
                      name="id"
                      defaultValue={row.original.id}
                      required
                    />
                    <Button
                      variant="destructive"
                      type="submit"
                      disabled={processing}
                      onClick={() => setData("id", row.original.id)}
                      asChild
                    >
                      <AlertDialogAction>Hapus</AlertDialogAction>
                    </Button>
                  </form>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </div>
        )
      },
    },
  ]
  const { userStudentData, subClassData, majorData, title } = usePage().props
  const { data, setData, post, processing, errors, reset } = useForm({
    fullname: "", nisn: "", email: "", phone: "", gender: "", religion: "",
    address: "", role: "", password: "", password_confirmation: "",
    sub_class_id: "", major: "", type: "", year: "",
  })
  const { toast } = useToast()
  const listSubClass = useMemo(() => subClassData, [])
  const listMajor = useMemo(() => majorData, [])
  const [tableUserStudentData, setTableUserStudentData] = useState(userStudentData)
  const [dialogCreate, setDialogCreate] = useState(false)

  function createUserStudentSubmit(e) {
    e.preventDefault()
    post("/admin/user", {
      onSuccess: () => {
        setDialogCreate(false)
        reset()
        toast({
          title: "Tambah Data Berhasil!",
          description: "Berhasil menambahkan data user",
        })
      }
    })
  }

  useEffect(() => {
    if (errors.message) {
      toast({
        title: errors.title,
        description: errors.message,
        variant: "destructive",
      })
    }
  }, [errors.message])

  useEffect(() => {
    setTableUserStudentData(userStudentData)
  }, [userStudentData])

  return (
    <DefaultLayout path={"/admin/user-siswa"} title={title}>
      <DataTable data={tableUserStudentData} columnsData={userStudentColumns}>
        <div className="flex justify-between">
          <CardTitle>Data User Siswa</CardTitle>
          <Dialog open={dialogCreate} onOpenChange={setDialogCreate}>
            <DialogTrigger asChild>
              <Button
                onClick={() => setData("role", "STUDENT")}
              >
                Tambah
              </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[600px]">
              <form onSubmit={createUserStudentSubmit}>
                <DialogHeader>
                  <DialogTitle>Tambah User Siswa</DialogTitle>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                  <div className="grid grid-cols-5 gap-4">
                    <div className="col-span-3 flex flex-col gap-2">
                      <Label htmlFor="fullname">
                        Nama Lengkap <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="fullname"
                        type="text"
                        placeholder="Nama Lengkap"
                        value={data.fullname}
                        onChange={e => setData("fullname", e.target.value)}
                        required
                      />
                    </div>
                    <div className="col-span-2 flex flex-col gap-2">
                      <Label htmlFor="nisn">
                        NISN <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="nisn"
                        type="text"
                        placeholder="NISN"
                        value={data.nisn}
                        onChange={e => setData("nisn", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="gender">
                        Jenis Kelamin <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="gender"
                        value={data.gender}
                        onValueChange={value => setData("gender", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Jenis Kelamin" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            ["Laki-laki", "Perempuan"].map((gender) => (
                              <SelectItem
                                key={gender}
                                value={gender}
                              >
                                {gender}
                              </SelectItem>
                            ))
                          }
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="religion">
                        Agama <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="religion"
                        value={data.religion}
                        onValueChange={value => setData("religion", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Agama" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            [
                              "Islam", "Kristen", "Katolik",
                              "Hindu", "Budha", "Konghucu",
                            ].map((religion) => (
                              <SelectItem
                                key={religion}
                                value={religion}
                              >
                                {religion}
                              </SelectItem>
                            ))
                          }
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                  <div className="flex flex-col gap-2">
                    <Label htmlFor="address">
                      Alamat <span className="text-red-500">*</span>
                    </Label>
                    <Textarea
                      id="address"
                      placeholder="Alamat"
                      value={data.address}
                      onChange={e => setData("address", e.target.value)}
                      required
                    />
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="email">
                        Email <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="email"
                        type="email"
                        placeholder="Email"
                        value={data.email}
                        onChange={e => setData("email", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="phone">
                        Telepon <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="phone"
                        type="text"
                        placeholder="Telepon"
                        value={data.phone}
                        onChange={e => setData("phone", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="password">
                        Password <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="password"
                        type="text"
                        placeholder="Password"
                        value={data.password}
                        onChange={e => setData("password", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="password_confirmation">
                        Konfirmasi Password <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="password_confirmation"
                        type="text"
                        placeholder="Konfirmasi Password"
                        value={data.password_confirmation}
                        onChange={e => setData("password_confirmation", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                  <div className="flex flex-col gap-2">
                    <Label htmlFor="sub_class_id">
                      Kelas <span className="text-red-500">*</span>
                    </Label>
                    <Select
                      id="sub_class_id"
                      value={data.sub_class_id}
                      onValueChange={value => setData("sub_class_id", value)}
                      required
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Pilih Kelas" />
                      </SelectTrigger>
                      <SelectContent>
                        {
                          listSubClass.map((kelas) => (
                            <SelectItem
                              key={kelas.id}
                              value={kelas.id}
                            >
                              {`${kelas.id} - ${kelas.name}`}
                            </SelectItem>
                          ))
                        }
                      </SelectContent>
                    </Select>
                  </div>
                  <div className="flex flex-col gap-2">
                    <Label htmlFor="major">
                      Jurusan <span className="text-red-500">*</span>
                    </Label>
                    <Select
                      id="major"
                      value={data.major}
                      onValueChange={value => setData("major", value)}
                      required
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Pilih Jurusan" />
                      </SelectTrigger>
                      <SelectContent>
                        {
                          listMajor.map((major) => (
                            <SelectItem
                              key={major.id}
                              value={major.id}
                            >
                              {`${major.id} - ${major.name}`}
                            </SelectItem>
                          ))
                        }
                      </SelectContent>
                    </Select>
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="type">
                        Tipe <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="type"
                        value={data.type}
                        onValueChange={value => setData("type", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Tipe" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            ["Umum", "SKTM", "Prestasi"].map((type) => (
                              <SelectItem
                                key={type}
                                value={type}
                              >
                                {type}
                              </SelectItem>
                            ))
                          }
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="year">
                        Tahun <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="year"
                        type="text"
                        placeholder="Tahun"
                        value={data.year}
                        onChange={e => setData("year", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                </div>
                <DialogFooter>
                  <Button type="submit" disabled={processing}>
                    Tambah
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>
      </DataTable>
    </DefaultLayout>
  )
}