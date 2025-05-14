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
import { useToast } from "@/components/ui/use-toast"

export default function SchoolClass() {
  const classColumns = [
    {
      accessorKey: "id",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            ID Kelas
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
      accessorKey: "name",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Nama Kelas
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
          id: "",
          name: "",
        })

        function updateClassSubmit(e) {
          e.preventDefault()
          put("/admin/class", {
            onSuccess: () => {
              setDialogUpdate(false)
              reset()
              toast({
                title: "Ubah Data Berhasil!",
                description: "Berhasil mengubah data kelas",
              })
            }
          })
        }

        function deleteClassSubmit(e) {
          e.preventDefault()
          destroy("/admin/class", {
            onSuccess: () => {
              toast({
                title: "Hapus Data Berhasil!",
                description: "Berhasil menghapus data kelas",
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
                    name: row.original.name
                  })}
                >
                  <PencilLine className="h-4 w-4" />
                </Button>
              </DialogTrigger>
              <DialogContent className="sm:max-w-[425px]">
                <form onSubmit={updateClassSubmit}>
                  <DialogHeader>
                    <DialogTitle>Ubah Kelas</DialogTitle>
                  </DialogHeader>
                  <div className="grid gap-4 py-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="name">
                        Nama Kelas <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="name"
                        type="text"
                        placeholder="Kelas"
                        defaultValue={row.original.name}
                        onChange={e => setData("name", e.target.value)}
                        required
                      />
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
                  <form onSubmit={deleteClassSubmit}>
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
  const { classData, title } = usePage().props
  const { data, setData, post, processing, errors, reset } = useForm({ name: "" })
  const { toast } = useToast()
  const [tableClassData, setTableClassData] = useState(classData)
  const [dialogCreate, setDialogCreate] = useState(false)

  function createClassSubmit(e) {
    e.preventDefault()
    post("/admin/class", {
      onSuccess: () => {
        setDialogCreate(false)
        reset()
        toast({
          title: "Tambah Data Berhasil!",
          description: "Berhasil menambahkan data kelas",
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
    setTableClassData(classData)
  }, [classData])

  return (
    <DefaultLayout path={"/admin/kelas"} title={title}>
      <DataTable data={tableClassData} columnsData={classColumns}>
        <div className="flex justify-between">
          <CardTitle>Data Kelas</CardTitle>
          <Dialog open={dialogCreate} onOpenChange={setDialogCreate}>
            <DialogTrigger asChild>
              <Button>Tambah</Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[425px]">
              <form onSubmit={createClassSubmit}>
                <DialogHeader>
                  <DialogTitle>Tambah Kelas</DialogTitle>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                  <div className="flex flex-col gap-2">
                    <Label htmlFor="name">
                      Nama Kelas <span className="text-red-500">*</span>
                    </Label>
                    <Input
                      id="name"
                      type="text"
                      placeholder="Kelas"
                      value={data.name}
                      onChange={e => setData("name", e.target.value)}
                      required
                    />
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