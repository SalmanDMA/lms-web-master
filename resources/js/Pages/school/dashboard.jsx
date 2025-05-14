// LAYOUT
import DefaultLayout from "@/components/layouts/default"

// ICONS
import {
  Activity,
  BookText,
  CircleUser,
  PcCase,
  SquareUser,
} from "lucide-react"

import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"

export default function SchoolDashboard({ dashboardData, title }) {
  return (
    <DefaultLayout path={"/admin/dashboard"} title={title}>
      <div className="grid gap-4 md:grid-cols-2 md:gap-8 lg:grid-cols-3">
        <Card className="bg-yellow-500 text-primary-foreground">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Jumlah Siswa
            </CardTitle>
            <CircleUser className="h-4 w-4" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{dashboardData.jumlah_siswa}</div>
            <p className="text-xs">
              +20.1% from last month
            </p>
          </CardContent>
        </Card>
        <Card className="bg-red-500 text-primary-foreground">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Jumlah Guru
            </CardTitle>
            <SquareUser className="h-4 w-4" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{dashboardData.jumlah_guru}</div>
            <p className="text-xs">
              +180.1% from last month
            </p>
          </CardContent>
        </Card>
        <Card className="bg-purple-500 text-primary-foreground">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Jumlah Pelajaran
            </CardTitle>
            <BookText className="h-4 w-4" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{dashboardData.jumlah_pelajaran}</div>
            <p className="text-xs">
              +19% from last month
            </p>
          </CardContent>
        </Card>
        <Card className="bg-green-500 text-primary-foreground">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Jumlah Kelas
            </CardTitle>
            <PcCase className="h-4 w-4" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{dashboardData.jumlah_kelas}</div>
            <p className="text-xs">
              +201 since last hour
            </p>
          </CardContent>
        </Card>
        <Card className="bg-cyan-500 text-primary-foreground">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Jumlah Jurusan
            </CardTitle>
            <Activity className="h-4 w-4" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{dashboardData.jumlah_jurusan}</div>
            <p className="text-xs">
              +201 since last hour
            </p>
          </CardContent>
        </Card>
      </div>
    </DefaultLayout>
  )
}