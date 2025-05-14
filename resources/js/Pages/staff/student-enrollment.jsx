import { useEffect, useState } from "react"
import { useForm, usePage } from "@inertiajs/react"

// LAYOUT
import DefaultLayout from "@/components/layouts/default"

// ICONS
import {
  ChevronDown,
  ChevronUp,
  ChevronsUpDown,
} from "lucide-react"

// UI
import { Button } from "@/components/ui/button"
import { CardTitle } from "@/components/ui/card"
import { Checkbox } from "@/components/ui/checkbox"
import DataTable from "@/components/ui/data-table"
import { useToast } from "@/components/ui/use-toast"

export default function StaffStudentEnrollment() {
  const userStudentColumns = [
    {
      id: "select",
      header: ({ table }) => (
        <Checkbox
          checked={
            table.getIsAllPageRowsSelected() ||
            (table.getIsSomePageRowsSelected() && "indeterminate")
          }
          onCheckedChange={(value) => {
            table.toggleAllPageRowsSelected(!!value)
            console.log(data)
          }}
          className="flex items-center border-white"
          aria-label="Select all"
        />
      ),
      cell: ({ row }) => (
        <Checkbox
          checked={row.getIsSelected()}
          onCheckedChange={(value) => {
            row.toggleSelected(!!value)
            if (value) {
              setData("students", data.students.push({ id: row.original.is_student.id }))
            } else {
              setData("students", data.students.splice(row.index, 1))
            }
            console.log(data)
          }}
          className="flex items-center"
          aria-label="Select row"
        />
      ),
      enableSorting: false,
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
  ]
  const { schoolExamData, userStudentData, title } = usePage().props
  const { data, setData, post, processing, errors, reset } = useForm({
    exam_id: schoolExamData.id, students: [],
  })
  const { toast } = useToast()
  const [tableUserStudentData, setTableUserStudentData] = useState(userStudentData)

  function createStudentEnrollmentSubmit(e) {
    e.preventDefault()
    post("/staff-curriculum/siswa-ujian", {
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
    setTableUserStudentData(userStudentData)
  }, [userStudentData])

  return (
    <DefaultLayout path={"/staff-curriculum/ujian"} title={title}>
      <form className="grid gap-4" onSubmit={createStudentEnrollmentSubmit}>
        <DataTable data={tableUserStudentData} columnsData={userStudentColumns}>
          <CardTitle>Tambah Siswa</CardTitle>
          <p className="text-muted-foreground">Ujian {schoolExamData.id}</p>
        </DataTable>
        <Button
          type="submit"
          disabled={processing}
          className="max-w-min justify-self-end"
        >
          Tambah
        </Button>
      </form>
    </DefaultLayout>
  )
}