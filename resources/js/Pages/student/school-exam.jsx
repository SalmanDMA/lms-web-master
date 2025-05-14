import { useEffect, useState } from "react"
import { useForm, usePage } from "@inertiajs/react"
import { useToast } from "@/components/ui/use-toast"

// LAYOUT
import DefaultLayout from "@/components/layouts/default"
import HeroStudent from "@/components/ui/hero-student"

// ICONS
import { BookA, ChevronDown, ChevronUp, ChevronsUpDown } from "lucide-react"

// UI
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

export default function StudentSchoolExam() {
  const schoolExamColumns = [
    {
      accessorKey: "exam_id.id",
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
      accessorKey: "exam_id.title",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Judul Ujian
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
      accessorKey: "exam_id.description",
      header: ({ column }) => {
        return (
          <Button
            variant="link"
            className="px-0 text-white"
            onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
          >
            Deskripsi Ujian
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
        const [dialogDetail, setDialogDetail] = useState(false)
        const { data, setData, post, processing, errors, reset } = useForm({
          token: "", status: "pengerjaan", school_exam_id: row.original.exam_id.id,
        })

        function verifyExamSubmit(e) {
          e.preventDefault()
          post(`/student/school-exam/${row.original.id}`, {
            onSuccess: () => {
              setDialogDetail(false)
              reset()
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
            <Dialog open={dialogDetail} onOpenChange={setDialogDetail}>
              <DialogTrigger asChild>
                <Button variant="outline" size="icon">
                  <BookA className="h-4 w-4" />
                </Button>
              </DialogTrigger>
              <DialogContent className="sm:max-w-[500px]">
                <form onSubmit={verifyExamSubmit}>
                  <DialogHeader>
                    <DialogTitle>Detail Ujian</DialogTitle>
                  </DialogHeader>
                  <div className="grid grid-cols-1 gap-4 pb-4 sm:grid-cols-2">
                    <div className="col-span-2">
                      <h2 className="text-2xl">{row.original.exam_id.title}</h2>
                      <h3 className="text-justify text-xs text-muted-foreground">
                        {`
                        Kelas ${row.original.exam_id.class_level} • ${row.original.exam_id.course}
                        • ${row.original.exam_id.academic_year} ${row.original.exam_id.semester}
                      `}
                      </h3>
                    </div>
                    <p className="col-span-2 text-justify text-sm">
                      {row.original.exam_id.description}
                    </p>
                    <div className="col-span-2 flex flex-col gap-2 text-sm">
                      <Label htmlFor="instruction" className="font-bold">
                        Instruksi
                      </Label>
                      <p id="instruction">
                        {row.original.exam_id.instruction}
                      </p>
                    </div>
                    <div className="flex flex-col gap-2">
                      <Label htmlFor="token" className="font-bold">
                        Token <span className="text-red-500">*</span>
                      </Label>
                      <Input
                        id="token"
                        type="text"
                        placeholder="Token"
                        onChange={e => setData("token", e.target.value)}
                        required
                      />
                    </div>
                  </div>
                  <DialogFooter>
                    <Button>Mulai</Button>
                  </DialogFooter>
                </form>
              </DialogContent>
            </Dialog>
          </div>
        )
      },
    },
  ]
  const { schoolExamData, title } = usePage().props
  const { toast } = useToast()

  return (
    <DefaultLayout title={title} padding={false}>
      <HeroStudent
        path={"/student/school-exam"}
        text="Selamat Datang di Materi Pembelajaran Anda!"
      />

      <div className="p-4 lg:p-6">
        <DataTable data={schoolExamData} columnsData={schoolExamColumns}>
          <div className="flex justify-between">
            <CardTitle>Data Ujian</CardTitle>
          </div>
        </DataTable>
      </div>
    </DefaultLayout>
  )
}