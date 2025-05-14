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
import { useToast } from "@/components/ui/use-toast"

export default function SchoolAcademicYear() {
  const academicYearColumns = [
    {
      accessorKey: "id",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            ID Tahun Akademik
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
      accessorKey: "year",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Tahun Akademik
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
        const { data, setData, put, delete: destroy, processing, errors, reset } = useForm({ id: "", year: "", status: "" })

        function updateAcademicYearSubmit(e) {
          e.preventDefault()
          put("/admin/academic-year", {
            onSuccess: () => {
              setDialogUpdate(false)
              reset()
              toast({
                title: "Ubah Data Berhasil!",
                description: "Berhasil mengubah data tahun akademik",
              })
            }
          })
        }

        function deleteAcademicYearSubmit(e) {
          e.preventDefault()
          destroy("/admin/academic-year", {
            onSuccess: () => {
              toast({
                title: "Hapus Data Berhasil!",
                description: "Berhasil menghapus data tahun akademik",
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
                    year: row.original.year,
                    status: row.original.status
                  })}
                >
                  <PencilLine className="h-4 w-4" />
                </Button>
              </DialogTrigger>
              <DialogContent className="sm:max-w-[425px]">
                <form onSubmit={updateAcademicYearSubmit}>
                  <DialogHeader>
                    <DialogTitle>Ubah Tahun Akademik</DialogTitle>
                  </DialogHeader>
                  <div className="grid gap-4 py-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="year">
                        Tahun Akademik <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="year"
                        type="text"
                        defaultValue={row.original.year}
                        onChange={e => setData("year", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="status">
                        Status <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="status"
                        name="status"
                        defaultValue={row.original.status}
                        onValueChange={value => setData("status", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Status" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            ["Aktif", "Tidak Aktif"].map((status) => (
                              <SelectItem
                                key={status}
                                value={status}
                              >
                                {status}
                              </SelectItem>
                            ))
                          }
                        </SelectContent>
                      </Select>
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
                  <form onSubmit={deleteAcademicYearSubmit}>
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
  const { academicYearData, title } = usePage().props
  const { data, setData, post, processing, errors, reset } = useForm({ year: "", status: "" })
  const { toast } = useToast()
  const [tableAcademicYearData, setTableAcademicYearData] = useState(academicYearData)
  const [dialogCreate, setDialogCreate] = useState(false)

  function createAcademicYearSubmit(e) {
    e.preventDefault()
    post("/admin/academic-year", {
      onSuccess: () => {
        setDialogCreate(false)
        reset()
        toast({
          title: "Tambah Data Berhasil!",
          description: "Berhasil menambahkan data tahun akademik",
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
    setTableAcademicYearData(academicYearData)
  }, [academicYearData])

  return (
    <DefaultLayout path={"/admin/tahun-akademik"} title={title}>
      <DataTable data={tableAcademicYearData} columnsData={academicYearColumns}>
        <div className="flex justify-between">
          <CardTitle>Data Tahun Akademik</CardTitle>
          <Dialog open={dialogCreate} onOpenChange={setDialogCreate}>
            <DialogTrigger asChild>
              <Button>Tambah</Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[425px]">
              <form onSubmit={createAcademicYearSubmit}>
                <DialogHeader>
                  <DialogTitle>Tambah Tahun Akademik</DialogTitle>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                  <div className="flex flex-col gap-2">
                    <Label htmlFor="year">
                      Tahun Akademik <span className="text-red-500">*</span>
                    </Label>
                    <Input
                      id="year"
                      type="text"
                      placeholder="YYYY/YYYY"
                      value={data.year}
                      onChange={e => setData("year", e.target.value)}
                      required
                    />
                  </div>
                  <div className="flex flex-col gap-2">
                    <Label htmlFor="status">
                      Status <span className="text-red-500">*</span>
                    </Label>
                    <Select
                      id="status"
                      name="status"
                      value={data.status}
                      onValueChange={value => setData("status", value)}
                      required
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Pilih Status" />
                      </SelectTrigger>
                      <SelectContent>
                        {
                          ["Aktif", "Tidak Aktif"].map((status) => (
                            <SelectItem
                              key={status}
                              value={status}
                            >
                              {status}
                            </SelectItem>
                          ))
                        }
                      </SelectContent>
                    </Select>
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