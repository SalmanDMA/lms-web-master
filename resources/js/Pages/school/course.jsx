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

export default function SchoolCourse() {
  const courseColumns = [
    {
      accessorKey: "id",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            ID Pelajaran
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
      accessorKey: "courses_title",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Nama Pelajaran
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
      accessorKey: "courses_description",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Deskripsi Pelajaran
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
      accessorKey: "type",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Tipe Pelajaran
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
      accessorKey: "curriculum",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Keterangan
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
        if (row.original.type === "Kurikulum") {
          return `Kurikulum ${row.original.curriculum}`
        } else {
          return row.original.curriculum
        }
      },
    },
    {
      accessorKey: "course_code",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Kode Pelajaran
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
          id: "", courses_title: "", courses_description: "", type: "", curriculum: "", course_code: "",
        })

        function updateCourseSubmit(e) {
          e.preventDefault()
          put("/admin/course", {
            onSuccess: () => {
              setDialogUpdate(false)
              reset()
              toast({
                title: "Ubah Data Berhasil!",
                description: "Berhasil mengubah data pelajaran",
              })
            }
          })
        }

        function deleteCourseSubmit(e) {
          e.preventDefault()
          destroy("/admin/course", {
            onSuccess: () => {
              toast({
                title: "Hapus Data Berhasil!",
                description: "Berhasil menghapus data pelajaran",
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
                    courses_title: row.original.courses_title,
                    courses_description: row.original.courses_description,
                    type: row.original.type,
                    curriculum: row.original.curriculum,
                    course_code: row.original.course_code,
                  })}
                >
                  <PencilLine className="h-4 w-4" />
                </Button>
              </DialogTrigger>
              <DialogContent className="sm:max-w-[600px]">
                <form onSubmit={updateCourseSubmit}>
                  <DialogHeader>
                    <DialogTitle>Ubah Pelajaran</DialogTitle>
                  </DialogHeader>
                  <div className="grid gap-4 py-4">
                    <div className="grid grid-cols-5 gap-4">
                      <div className="col-span-2 flex flex-col gap-2">
                        <Label htmlFor="type">
                          Tipe Pelajaran <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="type"
                          defaultValue={row.original.type}
                          onValueChange={value => setData("type", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Tipe" />
                          </SelectTrigger>
                          <SelectContent>
                            {
                              ["Kurikulum", "Lainnya"].map((type) => (
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
                      {data.type === "Kurikulum" && (
                        <div className="col-span-3 flex flex-col gap-2">
                          <Label htmlFor="curriculum">
                            Kurikulum <span className="text-red-500">*</span>
                          </Label>
                          <Select
                            id="curriculum"
                            defaultValue={row.original.curriculum}
                            onValueChange={value => setData("curriculum", value)}
                            required
                          >
                            <SelectTrigger>
                              <SelectValue placeholder="Pilih Kurikulum" />
                            </SelectTrigger>
                            <SelectContent>
                              {
                                ["K13", "Merdeka"].map((curriculum) => (
                                  <SelectItem
                                    key={curriculum}
                                    value={curriculum}
                                  >
                                    {curriculum}
                                  </SelectItem>
                                ))
                              }
                            </SelectContent>
                          </Select>
                        </div>
                      )}
                    </div>
                    <div className="grid grid-cols-3 gap-4">
                      <div className="col-span-2 flex flex-col gap-2">
                        <Label htmlFor="courses_title">
                          Nama Pelajaran <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="courses_title"
                          type="text"
                          placeholder="Pelajaran"
                          defaultValue={row.original.courses_title}
                          onChange={e => setData("courses_title", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="course_code">
                          Kode Pelajaran <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="course_code"
                          type="text"
                          placeholder="Kode Pelajaran"
                          defaultValue={row.original.course_code}
                          onChange={e => setData("course_code", e.target.value)}
                          required
                        />
                      </div>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="courses_description">
                        Deskripsi Pelajaran <span className="text-red-500">*</span>
                      </Label>
                      <Textarea
                        id="courses_description"
                        placeholder="Deskripsi Pelajaran"
                        defaultValue={row.original.courses_description}
                        onChange={e => setData("courses_description", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                  <DialogFooter>
                    <Button
                      type="submit"
                      disabled={processing}
                      onClick={() => {
                        if (data.type === "Lainnya") {
                          setData("curriculum", "Lainnya")
                        }
                      }}
                    >
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
                  <form onSubmit={deleteCourseSubmit}>
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
  const { courseData, title } = usePage().props
  const { data, setData, post, processing, errors, reset } = useForm({
    courses_title: "", courses_description: "", type: "", curriculum: "", course_code: "",
  })
  const { toast } = useToast()
  const [tableCourseData, setTableCourseData] = useState(courseData)
  const [dialogCreate, setDialogCreate] = useState(false)

  function createCourseSubmit(e) {
    e.preventDefault()
    post("/admin/course", {
      onSuccess: () => {
        setDialogCreate(false)
        reset()
        toast({
          title: "Tambah Data Berhasil!",
          description: "Berhasil menambahkan data pelajaran",
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
    setTableCourseData(courseData)
  }, [courseData])

  return (
    <DefaultLayout path={"/admin/pelajaran"} title={title}>
      <DataTable data={tableCourseData} columnsData={courseColumns}>
        <div className="flex justify-between">
          <CardTitle>Data Pelajaran</CardTitle>
          <Dialog open={dialogCreate} onOpenChange={setDialogCreate}>
            <DialogTrigger asChild>
              <Button>Tambah</Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[600px]">
              <form onSubmit={createCourseSubmit}>
                <DialogHeader>
                  <DialogTitle>Tambah Pelajaran</DialogTitle>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                  <div className="grid grid-cols-5 gap-4">
                    <div className="col-span-2 flex flex-col gap-2">
                      <Label htmlFor="type">
                        Tipe Pelajaran <span className="text-red-500">*</span>
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
                            ["Kurikulum", "Lainnya"].map((type) => (
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
                    {data.type === "Kurikulum" && (
                      <div className="col-span-3 flex flex-col gap-2">
                        <Label htmlFor="curriculum">
                          Kurikulum <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="curriculum"
                          value={data.curriculum}
                          onValueChange={value => setData("curriculum", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Kurikulum" />
                          </SelectTrigger>
                          <SelectContent>
                            {
                              ["K13", "Merdeka"].map((curriculum) => (
                                <SelectItem
                                  key={curriculum}
                                  value={curriculum}
                                >
                                  {curriculum}
                                </SelectItem>
                              ))
                            }
                          </SelectContent>
                        </Select>
                      </div>
                    )}
                  </div>
                  <div className="grid grid-cols-3 gap-4">
                    <div className="col-span-2 flex flex-col gap-2">
                      <Label htmlFor="courses_title">
                        Nama Pelajaran <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="courses_title"
                        type="text"
                        placeholder="Pelajaran"
                        value={data.courses_title}
                        onChange={e => setData("courses_title", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="course_code">
                        Kode Pelajaran <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="course_code"
                        type="text"
                        placeholder="Kode Pelajaran"
                        value={data.course_code}
                        onChange={e => setData("course_code", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                  <div className="flex flex-col gap-2">
                    <Label htmlFor="courses_description">
                      Deskripsi Pelajaran <span className="text-red-500">*</span>
                    </Label>
                    <Textarea
                      id="courses_description"
                      placeholder="Deskripsi Pelajaran"
                      value={data.courses_description}
                      onChange={e => setData("courses_description", e.target.value)}
                      required
                    />
                  </div>
                </div>
                <DialogFooter>
                  <Button
                    type="submit"
                    disabled={processing}
                    onClick={() => {
                      if (data.type === "Lainnya") {
                        setData("curriculum", "Lainnya")
                      }
                    }}
                  >
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