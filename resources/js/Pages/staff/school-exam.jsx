import { useEffect, useState } from "react"
import { Link, useForm, usePage } from "@inertiajs/react"

// LAYOUT
import DefaultLayout from "@/components/layouts/default"

// ICONS
import {
  ChevronDown,
  ChevronUp,
  ChevronsUpDown,
  PencilLine,
  Trash2,
  UserCog,
  Users,
} from "lucide-react"

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
import { Checkbox } from "@/components/ui/checkbox"
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

export default function StaffSchoolExam() {
  const schoolExamColumns = [
    {
      accessorKey: "id",
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
      accessorKey: "title",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Nama Ujian
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
            Tipe Ujian
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
      accessorKey: "course",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Pelajaran
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
        const [dialogRegisterTeacher, setDialogRegisterTeacher] = useState(false)
        const [dialogUpdate, setDialogUpdate] = useState(false)
        const { data, setData, post, put, delete: destroy, processing, errors, reset } = useForm({
          id: "", title: "", description: "", type: "", instruction: "", course: "", status: "",
          publication_status: "", class_level: "", academic_year: "", semester: "", start_time: "",
          end_time: "", token: "", duration: "", repeat_chance: "", device: "", maximum_user: "",
          is_random_question: 0, is_random_answer: 0, is_show_score: 0, is_show_result: 0,
          exam_id: "", teacher_id: "", role: "",
        })

        function registerTeacherSubmit(e) {
          e.preventDefault()
          post("/staff-curriculum/pengelola-ujian", {
            onSuccess: () => {
              setDialogRegisterTeacher(false)
              reset()
              toast({
                title: "Tambah Data Berhasil!",
                description: "Berhasil menambah data pengelola ujian",
              })
            }
          })
        }

        function updateSchoolExamSubmit(e) {
          e.preventDefault()
          put("/staff-curriculum/school-exam", {
            onSuccess: () => {
              setDialogUpdate(false)
              reset()
              toast({
                title: "Ubah Data Berhasil!",
                description: "Berhasil mengubah data ujian",
              })
            }
          })
        }

        function deleteSchoolExamSubmit(e) {
          e.preventDefault()
          destroy("/staff-curriculum/school-exam", {
            onSuccess: () => {
              toast({
                title: "Hapus Data Berhasil!",
                description: "Berhasil menghapus data ujian",
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
            <Link href={`/staff-curriculum/ujian/${row.original.id}/siswa`}>
              <Button
                className="text-blue-500 hover:text-blue-500"
                variant="outline"
                size="icon"
              >
                <Users className="h-4 w-4" />
              </Button>
            </Link>
            <Dialog open={dialogRegisterTeacher} onOpenChange={setDialogRegisterTeacher}>
              <DialogTrigger asChild>
                <Button
                  className="text-blue-500 hover:text-blue-500"
                  variant="outline"
                  size="icon"
                  onClick={() => setData({ exam_id: row.original.id })}
                >
                  <UserCog className="h-4 w-4" />
                </Button>
              </DialogTrigger>
              <DialogContent className="max-h-screen overflow-y-scroll sm:max-w-[425px]">
                <form onSubmit={registerTeacherSubmit}>
                  <DialogHeader>
                    <DialogTitle>Tambah Pengelola</DialogTitle>
                  </DialogHeader>
                  <div className="grid gap-4 py-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="teacher_id">
                        Guru <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="teacher_id"
                        onValueChange={value => setData("teacher_id", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Guru" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            userTeacherData.map((teacher) => (
                              <SelectItem
                                key={teacher.is_teacher.id}
                                value={teacher.is_teacher.id}
                              >
                                {teacher.fullname}
                              </SelectItem>
                            ))
                          }
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="role">
                        Role <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="role"
                        onValueChange={value => setData("role", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Role" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            ["Pengelola", "Penilai"].map((role) => (
                              <SelectItem
                                key={role.toUpperCase()}
                                value={role.toUpperCase()}
                              >
                                {role}
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
            <Dialog open={dialogUpdate} onOpenChange={setDialogUpdate}>
              <DialogTrigger asChild>
                <Button
                  className="text-yellow-500 hover:text-yellow-500"
                  variant="outline"
                  size="icon"
                  onClick={() => setData({
                    id: row.original.id, title: row.original.title,
                    description: row.original.description, type: row.original.type,
                    instruction: row.original.instruction, course: row.original.course,
                    status: row.original.status, publication_status: row.original.publication_status,
                    class_level: row.original.class_level, academic_year: row.original.academic_year,
                    semester: row.original.semester, start_time: row.original.start_time,
                    end_time: row.original.end_time, token: row.original.token,
                    duration: row.original.duration, repeat_chance: row.original.repeat_chance,
                    device: row.original.device, maximum_user: row.original.maximum_user,
                    is_random_question: row.original.is_random_question, is_random_answer: row.original.is_random_answer,
                    is_show_score: row.original.is_show_score, is_show_result: row.original.is_show_result,
                  })}
                >
                  <PencilLine className="h-4 w-4" />
                </Button>
              </DialogTrigger>
              <DialogContent className="max-h-screen overflow-y-scroll sm:max-w-[1000px]">
                <form onSubmit={updateSchoolExamSubmit}>
                  <DialogHeader>
                    <DialogTitle>Ubah Ujian</DialogTitle>
                  </DialogHeader>
                  <div className="grid gap-4 py-4">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-7">
                      <div className="flex flex-col gap-2 md:col-span-5">
                        <Label htmlFor="title">
                          Nama Ujian <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="title"
                          type="text"
                          placeholder="Nama Ujian"
                          defaultValue={row.original.title}
                          onChange={e => setData("title", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="type">
                          Tipe Ujian <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="type"
                          type="text"
                          placeholder="Tipe Ujian"
                          defaultValue={row.original.type}
                          onChange={e => setData("type", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="token">
                          Token
                        </Label>
                        <Input
                          id="token"
                          type="text"
                          placeholder="Token"
                          onChange={e => setData("token", e.target.value)}
                        />
                      </div>
                    </div>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="description">
                          Deskripsi Ujian <span className="text-red-500">*</span>
                        </Label>
                        <Textarea
                          id="description"
                          placeholder="Deskripsi Ujian"
                          defaultValue={row.original.description}
                          onChange={e => setData("description", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="instruction">
                          Instruksi Ujian <span className="text-red-500">*</span>
                        </Label>
                        <Textarea
                          id="instruction"
                          placeholder="Instruksi Ujian"
                          defaultValue={row.original.instruction}
                          onChange={e => setData("instruction", e.target.value)}
                          required
                        />
                      </div>
                    </div>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-6">
                      <div className="flex flex-col gap-2 md:col-span-3">
                        <Label htmlFor="course">
                          Pelajaran <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="course"
                          type="text"
                          placeholder="Pelajaran"
                          defaultValue={row.original.course}
                          onChange={e => setData("course", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="class_level">
                          Level Kelas <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="class_level"
                          type="text"
                          placeholder="Level Kelas"
                          defaultValue={row.original.class_level}
                          onChange={e => setData("class_level", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="status">
                          Status <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="status"
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
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="publication_status">
                          Status Publikasi <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="publication_status"
                          defaultValue={row.original.publication_status}
                          onValueChange={value => setData("publication_status", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Status Publikasi" />
                          </SelectTrigger>
                          <SelectContent>
                            {
                              ["Published", "Unpublished"].map((publication_status) => (
                                <SelectItem
                                  key={publication_status}
                                  value={publication_status}
                                >
                                  {publication_status}
                                </SelectItem>
                              ))
                            }
                          </SelectContent>
                        </Select>
                      </div>
                    </div>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="academic_year">
                          Tahun Akademik <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="academic_year"
                          type="text"
                          placeholder="YYYY/YYYY"
                          defaultValue={row.original.academic_year}
                          onChange={e => setData("academic_year", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="semester">
                          Semester <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="semester"
                          type="text"
                          placeholder="Semester"
                          defaultValue={row.original.semester}
                          onChange={e => setData("semester", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="start_time">
                          Waktu Mulai <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="start_time"
                          type="datetime-local"
                          defaultValue={row.original.examSetting.start_time}
                          onChange={e => setData("start_time", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="end_time">
                          Waktu Selesai <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="end_time"
                          type="datetime-local"
                          defaultValue={row.original.examSetting.end_time}
                          onChange={e => setData("end_time", e.target.value)}
                          required
                        />
                      </div>
                    </div>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="duration">
                          Durasi <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="duration"
                          type="text"
                          pattern="(?:[01]|2(?![4-9])){1}\d{1}:[0-5]{1}\d{1}"
                          placeholder="HH:MM"
                          defaultValue={row.original.examSetting.duration.slice(0, 5)}
                          onChange={e => setData("duration", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="repeat_chance">
                          Kesempatan Ulang <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="repeat_chance"
                          type="number"
                          placeholder="Kesempatan Ulang"
                          defaultValue={row.original.examSetting.repeat_chance}
                          onChange={e => setData("repeat_chance", e.target.value)}
                          required
                        />
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="device">
                          Perangkat <span className="text-red-500">*</span>
                        </Label>
                        <Select
                          id="device"
                          defaultValue={row.original.examSetting.device}
                          onValueChange={value => setData("device", value)}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih Perangkat" />
                          </SelectTrigger>
                          <SelectContent>
                            {
                              ["Web", "Mobile"].map((device) => (
                                <SelectItem
                                  key={device}
                                  value={device}
                                >
                                  {device}
                                </SelectItem>
                              ))
                            }
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="flex flex-col gap-2">
                        <Label htmlFor="maximum_user">
                          Maksimum User <span className="text-red-500">*</span>
                        </Label>
                        <Input
                          id="maximum_user"
                          type="number"
                          placeholder="Maksimum User"
                          defaultValue={row.original.examSetting.maximum_user}
                          onChange={e => setData("maximum_user", e.target.value)}
                          required
                        />
                      </div>
                    </div>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                      <div className="flex items-center space-x-2">
                        <Checkbox
                          id="is_random_question"
                          defaultChecked={row.original.examSetting.is_random_question}
                          onCheckedChange={value => setData("is_random_question", value)}
                        />
                        <label
                          htmlFor="is_random_question"
                          className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                          Pertanyaan Diacak
                        </label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox
                          id="is_random_answer"
                          defaultChecked={row.original.examSetting.is_random_answer}
                          onCheckedChange={value => setData("is_random_answer", value)}
                        />
                        <label
                          htmlFor="is_random_answer"
                          className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                          Jawaban Diacak
                        </label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox
                          id="is_show_score"
                          defaultChecked={row.original.examSetting.is_show_score}
                          onCheckedChange={value => setData("is_show_score", value)}
                        />
                        <label
                          htmlFor="is_show_score"
                          className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                          Tampilkan Skor
                        </label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Checkbox
                          id="is_show_result"
                          defaultChecked={row.original.examSetting.is_show_result}
                          onCheckedChange={value => setData("is_show_result", value)}
                        />
                        <label
                          htmlFor="is_show_result"
                          className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                          Tampilkan Hasil
                        </label>
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
                  <form onSubmit={deleteSchoolExamSubmit}>
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
  const { schoolExamData, userTeacherData, userStudentData, title } = usePage().props
  const { data, setData, post, processing, errors, reset } = useForm({
    title: "", description: "", type: "", instruction: "", course: "", status: "",
    publication_status: "", class_level: "", academic_year: "", semester: "", start_time: "",
    end_time: "", token: "", duration: "", repeat_chance: "", device: "", maximum_user: "",
    is_random_question: 0, is_random_answer: 0, is_show_score: 0, is_show_result: 0,
  })
  const { toast } = useToast()
  const [tableSchoolExamData, setTableSchoolExamData] = useState(schoolExamData)
  const [dialogCreate, setDialogCreate] = useState(false)

  function createSchoolExamSubmit(e) {
    e.preventDefault()
    post("/staff-curriculum/school-exam", {
      onSuccess: () => {
        setDialogCreate(false)
        reset()
        toast({
          title: "Tambah Data Berhasil!",
          description: "Berhasil menambahkan data ujian",
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
    setTableSchoolExamData(schoolExamData)
  }, [schoolExamData])

  return (
    <DefaultLayout path={"/staff-curriculum/ujian"} title={title}>
      <DataTable data={tableSchoolExamData} columnsData={schoolExamColumns}>
        <div className="flex justify-between">
          <CardTitle>Data Ujian</CardTitle>
          <Dialog open={dialogCreate} onOpenChange={setDialogCreate}>
            <DialogTrigger asChild>
              <Button>Tambah</Button>
            </DialogTrigger>
            <DialogContent className="max-h-screen overflow-y-scroll sm:max-w-[1000px]">
              <form onSubmit={createSchoolExamSubmit}>
                <DialogHeader>
                  <DialogTitle>Tambah Ujian</DialogTitle>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                  <div className="grid grid-cols-1 gap-4 md:grid-cols-7">
                    <div className="flex flex-col gap-2 md:col-span-5">
                      <Label htmlFor="title">
                        Nama Ujian <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="title"
                        type="text"
                        placeholder="Nama Ujian"
                        value={data.title}
                        onChange={e => setData("title", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="type">
                        Tipe Ujian <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="type"
                        type="text"
                        placeholder="Tipe Ujian"
                        value={data.type}
                        onChange={e => setData("type", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="token">
                        Token <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="token"
                        type="text"
                        placeholder="Token"
                        value={data.token}
                        onChange={e => setData("token", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                  <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="description">
                        Deskripsi Ujian <span className="text-red-500">*</span>
                      </Label>
                      <Textarea
                        id="description"
                        placeholder="Deskripsi Ujian"
                        value={data.description}
                        onChange={e => setData("description", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="instruction">
                        Instruksi Ujian <span className="text-red-500">*</span>
                      </Label>
                      <Textarea
                        id="instruction"
                        placeholder="Instruksi Ujian"
                        value={data.instruction}
                        onChange={e => setData("instruction", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                  <div className="grid grid-cols-1 gap-4 md:grid-cols-6">
                    <div className="flex flex-col gap-2 md:col-span-3">
                      <Label htmlFor="course">
                        Pelajaran <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="course"
                        type="text"
                        placeholder="Pelajaran"
                        value={data.course}
                        onChange={e => setData("course", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="class_level">
                        Level Kelas <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="class_level"
                        type="text"
                        placeholder="Level Kelas"
                        value={data.class_level}
                        onChange={e => setData("class_level", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="status">
                        Status <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="status"
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
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="publication_status">
                        Status Publikasi <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="publication_status"
                        value={data.publication_status}
                        onValueChange={value => setData("publication_status", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Status Publikasi" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            ["Published", "Unpublished"].map((publication_status) => (
                              <SelectItem
                                key={publication_status}
                                value={publication_status}
                              >
                                {publication_status}
                              </SelectItem>
                            ))
                          }
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                  <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="academic_year">
                        Tahun Akademik <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="academic_year"
                        type="text"
                        placeholder="YYYY/YYYY"
                        value={data.academic_year}
                        onChange={e => setData("academic_year", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="semester">
                        Semester <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="semester"
                        type="text"
                        placeholder="Semester"
                        value={data.semester}
                        onChange={e => setData("semester", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="start_time">
                        Waktu Mulai <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="start_time"
                        type="datetime-local"
                        value={data.start_time}
                        onChange={e => setData("start_time", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="end_time">
                        Waktu Selesai <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="end_time"
                        type="datetime-local"
                        value={data.end_time}
                        onChange={e => setData("end_time", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                  <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="duration">
                        Durasi <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="duration"
                        type="text"
                        pattern="(?:[01]|2(?![4-9])){1}\d{1}:[0-5]{1}\d{1}"
                        placeholder="HH:MM"
                        value={data.duration.slice(0, 5)}
                        onChange={e => setData("duration", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="repeat_chance">
                        Kesempatan Ulang <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="repeat_chance"
                        type="number"
                        placeholder="Kesempatan Ulang"
                        value={data.repeat_chance}
                        onChange={e => setData("repeat_chance", e.target.value)}
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="device">
                        Perangkat <span className="text-red-500">*</span>
                      </Label>
                      <Select
                        id="device"
                        value={data.device}
                        onValueChange={value => setData("device", value)}
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Perangkat" />
                        </SelectTrigger>
                        <SelectContent>
                          {
                            ["Web", "Mobile"].map((device) => (
                              <SelectItem
                                key={device}
                                value={device}
                              >
                                {device}
                              </SelectItem>
                            ))
                          }
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="maximum_user">
                        Maksimum User <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="maximum_user"
                        type="number"
                        placeholder="Maksimum User"
                        value={data.maximum_user}
                        onChange={e => setData("maximum_user", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                  <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div className="flex items-center space-x-2">
                      <Checkbox
                        id="is_random_question"
                        checked={data.is_random_question}
                        onCheckedChange={value => setData("is_random_question", value)}
                      />
                      <label
                        htmlFor="is_random_question"
                        className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                      >
                        Pertanyaan Diacak
                      </label>
                    </div>
                    <div className="flex items-center space-x-2">
                      <Checkbox
                        id="is_random_answer"
                        checked={data.is_random_answer}
                        onCheckedChange={value => setData("is_random_answer", value)}
                      />
                      <label
                        htmlFor="is_random_answer"
                        className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                      >
                        Jawaban Diacak
                      </label>
                    </div>
                    <div className="flex items-center space-x-2">
                      <Checkbox
                        id="is_show_score"
                        checked={data.is_show_score}
                        onCheckedChange={value => setData("is_show_score", value)}
                      />
                      <label
                        htmlFor="is_show_score"
                        className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                      >
                        Tampilkan Skor
                      </label>
                    </div>
                    <div className="flex items-center space-x-2">
                      <Checkbox
                        id="is_show_result"
                        checked={data.is_show_result}
                        onCheckedChange={value => setData("is_show_result", value)}
                      />
                      <label
                        htmlFor="is_show_result"
                        className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                      >
                        Tampilkan Hasil
                      </label>
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