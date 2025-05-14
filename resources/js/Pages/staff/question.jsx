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

export default function StaffQuestion() {
  const questionColumns = [
    {
      accessorKey: "id",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            ID Soal
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
      accessorKey: "school_exam_id",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            ID Ujian
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
      accessorKey: "question_text",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Pertanyaan
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
      accessorKey: "question_type",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Jenis
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
      accessorKey: "difficult",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Kesulitan
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
      accessorKey: "point",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Poin
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
          id: "", question_text: "", question_type: "", point: "", grade_method: "",
        })

        function updateQuestionSubmit(e) {
          e.preventDefault()
          put("/staff-curriculum/question", {
            onSuccess: () => {
              setDialogUpdate(false)
              reset()
              toast({
                title: "Ubah Data Berhasil!",
                description: "Berhasil mengubah data soal",
              })
            }
          })
        }

        function deleteQuestionSubmit(e) {
          e.preventDefault()
          destroy("/staff-curriculum/question", {
            onSuccess: () => {
              toast({
                title: "Hapus Data Berhasil!",
                description: "Berhasil menghapus data soal",
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
                    id: row.original.id, exam_id: row.original.exam_id,
                    name: row.original.name, description: row.original.description,
                  })}
                >
                  <PencilLine className="h-4 w-4" />
                </Button>
              </DialogTrigger>
              <DialogContent className="max-h-screen overflow-y-scroll sm:max-w-[875px]">
                <form onSubmit={updateQuestionSubmit}>
                  <DialogHeader>
                    <DialogTitle>Ubah Bagian Ujian</DialogTitle>
                  </DialogHeader>
                  <div className="grid gap-4 py-4">
                    <div className="grid grid-cols-2 gap-4">
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="school_exam_id">
                          Ujian <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="school_exam_id"
                          defaultValue={row.original.school_exam_id}
                          onValueChange={value => setData("school_exam_id", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Ujian" />
                          </SelectTrigger>
                          <SelectContent>
                            {listSchoolExam.map((schoolExam) => (
                              <SelectItem
                                key={schoolExam.id}
                                value={schoolExam.id}
                              >
                                {`${schoolExam.id} - ${schoolExam.title}`}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="section_id">
                          Bagian Ujian <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="section_id"
                          defaultValue={row.original.section_id}
                          onValueChange={value => setData("section_id", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Bagian Ujian" />
                          </SelectTrigger>
                          <SelectContent>
                            {listExamSection.map((examSection) => (
                              <SelectItem
                                key={examSection.id}
                                value={examSection.id}
                              >
                                {`${examSection.id} - ${examSection.name}`}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="question_type">
                          Jenis Soal <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="question_type"
                          defaultValue={row.original.question_type}
                          onValueChange={value => setData("question_type", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Jenis Soal" />
                          </SelectTrigger>
                          <SelectContent>
                            {[
                              "Pilihan Ganda",
                              "Pilihan Ganda Complex",
                              "True False",
                              "Essay",
                            ].map((jenis) => (
                              <SelectItem
                                key={jenis}
                                value={jenis}
                              >
                                {jenis}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="grade_method">
                          Metode Penilaian <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="grade_method"
                          type="text"
                          placeholder="Metode Penilaian"
                          defaultValue={row.original.grade_method}
                          onChange={e => setData("grade_method", e.target.value)}
                          required
                        />
                      </div>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="point">
                          Poin <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="point"
                          type="number"
                          placeholder="Poin"
                          defaultValue={row.original.point}
                          onChange={e => setData("point", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="difficult">
                          Kesulitan <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="difficult"
                          defaultValue={row.original.difficult}
                          onValueChange={value => setData("difficult", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Kesulitan" />
                          </SelectTrigger>
                          <SelectContent>
                            {[
                              "Easy",
                              "Medium",
                              "Hard",
                            ].map((kesulitan) => (
                              <SelectItem
                                key={kesulitan}
                                value={kesulitan}
                              >
                                {kesulitan}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="question_type">
                        Pertanyaan <span className="text-red-500">*</span>
                      </Label>
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
                  <form onSubmit={deleteQuestionSubmit}>
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
  const { examSectionData, questionData, schoolExamData, title } = usePage().props
  const { data, setData, post, processing, errors, reset } = useForm({
    question_text: "", question_type: "", point: "", grade_method: "",
  })
  const { toast } = useToast()
  const listExamSection = useMemo(() => examSectionData, [])
  const listSchoolExam = useMemo(() => schoolExamData, [])
  const [tableQuestionData, setTableQuestionData] = useState(questionData)
  const [dialogCreate, setDialogCreate] = useState(false)

  function createQuestionSubmit(e) {
    e.preventDefault()
    post("/staff-curriculum/question", {
      onSuccess: () => {
        setDialogCreate(false)
        reset()
        toast({
          title: "Tambah Data Berhasil!",
          description: "Berhasil menambahkan data soal",
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
    setTableQuestionData(questionData)
  }, [questionData])

  return (
    <DefaultLayout path={"/staff-curriculum/soal"} title={title}>
      <DataTable data={tableQuestionData} columnsData={questionColumns}>
        <div className="flex justify-between">
          <CardTitle>Data Soal</CardTitle>
          <Dialog open={dialogCreate} onOpenChange={setDialogCreate}>
            <DialogTrigger asChild>
              <Button>Tambah</Button>
            </DialogTrigger>
            <DialogContent className="max-h-screen overflow-y-scroll sm:max-w-[875px]">
              <form onSubmit={createQuestionSubmit}>
                <DialogHeader>
                  <DialogTitle>Tambah Soal</DialogTitle>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                  <div className="grid grid-cols-2 gap-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="school_exam_id">
                        Ujian <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="school_exam_id"
                        value={data.school_exam_id}
                        onValueChange={value => setData("school_exam_id", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Ujian" />
                        </SelectTrigger>
                        <SelectContent>
                          {listSchoolExam.map((schoolExam) => (
                            <SelectItem
                              key={schoolExam.id}
                              value={schoolExam.id}
                            >
                              {`${schoolExam.id} - ${schoolExam.title}`}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="section_id">
                        Bagian Ujian <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="section_id"
                        value={data.section_id}
                        onValueChange={value => setData("section_id", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Bagian Ujian" />
                        </SelectTrigger>
                        <SelectContent>
                          {listExamSection.map((examSection) => (
                            <SelectItem
                              key={examSection.id}
                              value={examSection.id}
                            >
                              {`${examSection.id} - ${examSection.name}`}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="question_type">
                        Jenis Soal <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="question_type"
                        value={data.question_type}
                        onValueChange={value => setData("question_type", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Jenis Soal" />
                        </SelectTrigger>
                        <SelectContent>
                          {[
                            "Pilihan Ganda",
                            "Pilihan Ganda Complex",
                            "True False",
                            "Essay",
                          ].map((jenis) => (
                            <SelectItem
                              key={jenis}
                              value={jenis}
                            >
                              {jenis}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="grade_method">
                        Metode Penilaian <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="grade_method"
                        type="text"
                        placeholder="Metode Penilaian"
                        value={data.grade_method}
                        onChange={e => setData("grade_method", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="point">
                        Poin <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="point"
                        type="number"
                        placeholder="Poin"
                        value={data.point}
                        onChange={e => setData("point", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="difficult">
                        Kesulitan <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="difficult"
                        value={data.difficult}
                        onValueChange={value => setData("difficult", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Kesulitan" />
                        </SelectTrigger>
                        <SelectContent>
                          {[
                            "Easy",
                            "Medium",
                            "Hard",
                          ].map((kesulitan) => (
                            <SelectItem
                              key={kesulitan}
                              value={kesulitan}
                            >
                              {kesulitan}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                  <div className="flex flex-col gap-2">
                    <Label htmlFor="question_type">
                      Pertanyaan <span className="text-red-500">*</span>
                    </Label>
                    <App></App>
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