import { useEffect, useState } from "react"
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

export default function SchoolUserStaff() {
  const userStaffColumns = [
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
      accessorKey: "is_staff.nip",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            NIP
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
          id: "", fullname: "", nip: "", email: "", phone: "", gender: "", religion: "",
          address: "", role: "", password: "", password_confirmation: "",
          placement: "", authority: "",
        })

        function updateUserStaffSubmit(e) {
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

        function deleteUserStaffSubmit(e) {
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
                    nip: row.original.is_staff.nip,
                    email: row.original.email,
                    phone: row.original.phone,
                    gender: row.original.gender,
                    religion: row.original.religion,
                    address: row.original.address,
                    role: row.original.role,
                    placement: row.original.is_staff.placement,
                    authority: row.original.is_staff.authority,
                  })}
                >
                  <PencilLine className="h-4 w-4" />
                </Button>
              </DialogTrigger>
              <DialogContent className="sm:max-w-[600px]">
                <form onSubmit={updateUserStaffSubmit}>
                  <DialogHeader>
                    <DialogTitle>Ubah User Staff</DialogTitle>
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
                        <Label htmlFor="nip">
                          NIP <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="nip"
                          type="text"
                          placeholder="NIP"
                          defaultValue={row.original.is_staff.nip}
                          onChange={e => setData("nip", e.target.value)}
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
                    <div className="grid grid-cols-2 gap-4">
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="authority">
                          Otoritas <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="authority"
                          defaultValue={row.original.is_staff.authority}
                          onValueChange={value => setData("authority", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Otoritas" />
                          </SelectTrigger>
                          <SelectContent>
                            {
                              [
                                { label: "Staff Admin", value: "ADMIN" },
                                { label: "Staff Kurikulum", value: "KURIKULUM" },
                              ].map((staff) => (
                                <SelectItem
                                  key={staff.value}
                                  value={staff.value}
                                >
                                  {staff.label}
                                </SelectItem>
                              ))
                            }
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="placement">
                          Penempatan <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="placement"
                          type="text"
                          placeholder="Penempatan"
                          defaultValue={row.original.is_staff.placement}
                          onChange={e => setData("placement", e.target.value)}
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
                  <form onSubmit={deleteUserStaffSubmit}>
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
  const { userStaffData, title } = usePage().props
  const { data, setData, post, processing, errors, reset } = useForm({
    fullname: "", nip: "", email: "", phone: "", gender: "", religion: "",
    address: "", role: "", password: "", password_confirmation: "",
    placement: "", authority: "",
  })
  const { toast } = useToast()
  const [tableUserStaffData, setTableUserStaffData] = useState(userStaffData)
  const [dialogCreate, setDialogCreate] = useState(false)

  function createUserStaffSubmit(e) {
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
    setTableUserStaffData(userStaffData)
  }, [userStaffData])

  return (
    <DefaultLayout path={"/admin/user-staff"} title={title}>
      <DataTable data={tableUserStaffData} columnsData={userStaffColumns}>
        <div className="flex justify-between">
          <CardTitle>Data User Staff</CardTitle>
          <Dialog open={dialogCreate} onOpenChange={setDialogCreate}>
            <DialogTrigger asChild>
              <Button
                onClick={() => setData("role", "STAFF")}
              >
                Tambah
              </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[600px]">
              <form onSubmit={createUserStaffSubmit}>
                <DialogHeader>
                  <DialogTitle>Tambah User Staff</DialogTitle>
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
                      <Label htmlFor="nip">
                        NIP <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="nip"
                        type="text"
                        placeholder="NIP"
                        value={data.nip}
                        onChange={e => setData("nip", e.target.value)}
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
                  <div className="grid grid-cols-2 gap-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="authority">
                        Otoritas <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="authority"
                        value={data.authority}
                        onValueChange={value => setData("authority", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Otoritas" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            [
                              { label: "Staff Admin", value: "ADMIN" },
                              { label: "Staff Kurikulum", value: "KURIKULUM" },
                            ].map((staff) => (
                              <SelectItem
                                key={staff.value}
                                value={staff.value}
                              >
                                {staff.label}
                              </SelectItem>
                            ))
                          }
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="placement">
                        Penempatan <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="placement"
                        type="text"
                        placeholder="Penempatan"
                        value={data.placement}
                        onChange={e => setData("placement", e.target.value)}
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