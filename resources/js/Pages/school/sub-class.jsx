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
import { useToast } from "@/components/ui/use-toast"

export default function SchoolClass() {
  const subClassColumns = [
    {
      accessorKey: "class_id",
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
      cell: ({ row }) => {
        let className
        listClass.map((kelas) => {
          if (kelas.id === row.original.class_id) {
            className = `${kelas.id} - ${kelas.name}`
          }
        })
        return className
      },
    },
    {
      accessorKey: "id",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            ID Sub Kelas
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
            Nama Sub Kelas
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
      accessorKey: "guardian",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Guardian
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
          id: "", class_id: "", name: "", guardian: "",
        })

        function updateSubClassSubmit(e) {
          e.preventDefault()
          put("/admin/sub-class", {
            onSuccess: () => {
              setDialogUpdate(false)
              reset()
              toast({
                title: "Ubah Data Berhasil!",
                description: "Berhasil mengubah data sub kelas",
              })
            }
          })
        }

        function deleteSubClassSubmit(e) {
          e.preventDefault()
          destroy("/admin/sub-class", {
            onSuccess: () => {
              toast({
                title: "Hapus Data Berhasil!",
                description: "Berhasil menghapus data sub kelas",
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
                    class_id: row.original.class_id,
                    name: row.original.name,
                    guardian: row.original.guardian
                  })}
                >
                  <PencilLine className="h-4 w-4" />
                </Button>
              </DialogTrigger>
              <DialogContent className="sm:max-w-[425px]">
                <form onSubmit={updateSubClassSubmit}>
                  <DialogHeader>
                    <DialogTitle>Ubah Kelas</DialogTitle>
                  </DialogHeader>
                  <div className="grid gap-4 py-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="class_id">
                        Kelas <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="class_id"
                        name="class_id"
                        defaultValue={row.original.class_id}
                        onValueChange={value => setData("class_id", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Kelas" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            listClass.map((kelas) => (
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
                      <Label htmlFor="name">
                        Nama Sub Kelas <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="name"
                        type="text"
                        placeholder="Sub Kelas"
                        defaultValue={row.original.name}
                        onChange={e => setData({
                          id: row.original.id,
                          name: e.target.value
                        })}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="guardian">
                        Nama Guardian <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="guardian"
                        type="text"
                        placeholder="Guardian"
                        defaultValue={row.original.guardian}
                        onChange={e => setData("guardian", e.target.value)}
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
                  <form onSubmit={deleteSubClassSubmit}>
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
  const { classData, subClassData, title } = usePage().props
  const { data, setData, post, processing, errors, reset } = useForm({
    class_id: "", name: "", guardian: "",
  })
  const { toast } = useToast()
  const listClass = useMemo(() => classData, [])
  const [tableSubClassData, setTableSubClassData] = useState(subClassData)
  const [dialogCreate, setDialogCreate] = useState(false)

  function createSubClassSubmit(e) {
    e.preventDefault()
    post("/admin/sub-class", {
      onSuccess: () => {
        setDialogCreate(false)
        reset()
        toast({
          title: "Tambah Data Berhasil!",
          description: "Berhasil menambahkan data sub kelas",
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
    setTableSubClassData(subClassData)
  }, [subClassData])

  return (
    <DefaultLayout path={"/admin/sub-kelas"} title={title}>
      <DataTable data={tableSubClassData} columnsData={subClassColumns}>
        <div className="flex justify-between">
          <CardTitle>Data Sub Kelas</CardTitle>
          <Dialog open={dialogCreate} onOpenChange={setDialogCreate}>
            <DialogTrigger asChild>
              <Button>Tambah</Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[425px]">
              <form onSubmit={createSubClassSubmit}>
                <DialogHeader>
                  <DialogTitle>Tambah Sub Kelas</DialogTitle>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                  <div className="flex flex-col gap-2">
                    <Label htmlFor="class_id">
                      Kelas <span className="text-red-500">*</span>
                    </Label>
                    <Select
                      id="class_id"
                      name="class_id"
                      onValueChange={value => setData("class_id", value)}
                      required
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Pilih Kelas" />
                      </SelectTrigger>
                      <SelectContent>
                        {
                          listClass.map((kelas) => (
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
                    <Label htmlFor="name">
                      Nama Sub Kelas <span className="text-red-500">*</span>
                    </Label>
                    <Input
                      id="name"
                      type="text"
                      placeholder="Sub Kelas"
                      value={data.name}
                      onChange={e => setData("name", e.target.value)}
                      required
                    />
                  </div>
                  <div className="flex flex-col gap-2">
                    <Label htmlFor="guardian">
                      Nama Guardian <span className="text-red-500">*</span>
                    </Label>
                    <Input
                      id="guardian"
                      type="text"
                      placeholder="Guardian"
                      value={data.guardian}
                      onChange={e => setData("guardian", e.target.value)}
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