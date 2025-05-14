import { Link } from "@inertiajs/react"
import { useTimer } from "react-timer-hook"

// ICONS
import {
  BookA,
  BookOpenText,
  NotebookText,
  Timer,
} from "lucide-react"

// UI
import {
  Card,
  CardContent,
} from "@/components/ui/card"

export default function HeroStudent({ path, text = "", user }) {
  return (
    <div className="rounded-bl-[3rem] bg-primary p-8 md:rounded-none">
      <div className="mx-auto grid max-w-[600px] grid-cols-3 gap-6">
        <h1 className="col-span-3 text-center text-2xl font-bold text-primary-foreground">
          {text || `Hello, ${user}`}
        </h1>
        <Link href="/student/material">
          <Card className={`
            cursor-pointer border-none transition-all
            ${path === "/student/material" ? "bg-blue-900" : "hover:bg-blue-900"}
          `}>
            <CardContent className={`
              flex flex-col items-center gap-3 p-4 md:p-6
              ${path === "/student/material" ? "text-primary-foreground" : "group"}
            `}>
              <BookOpenText className={`
                h-10 w-10 md:h-20 md:w-20
                ${path === "/student/material" ? "" : "text-blue-900 group-hover:text-primary-foreground"}
              `} />
              <div className={`
                text-sm font-bold md:text-lg
                ${path === "/student/material" ? "" : "text-foreground group-hover:text-primary-foreground"}
              `}>
                Materi
              </div>
            </CardContent>
          </Card>
        </Link>
        <Link>
          <Card className={`
            cursor-pointer border-none transition-all
            ${path === "/student/assignment" ? "bg-blue-900" : "hover:bg-blue-900"}
          `}>
            <CardContent className={`
              flex flex-col items-center gap-3 p-4 md:p-6
              ${path === "/student/assignment" ? "text-primary-foreground" : "group"}
            `}>
              <NotebookText className={`
                h-10 w-10 md:h-20 md:w-20
                ${path === "/student/assignment" ? "" : "text-blue-900 group-hover:text-primary-foreground"}
              `} />
              <div className={`
                text-sm font-bold md:text-lg
                ${path === "/student/assignment" ? "" : "text-foreground group-hover:text-primary-foreground"}
              `}>
                Tugas
              </div>
            </CardContent>
          </Card>
        </Link>
        <Link href="/student/school-exam">
          <Card className={`
            cursor-pointer border-none transition-all
            ${path === "/student/school-exam" ? "bg-blue-900" : "hover:bg-blue-900"}
          `}>
            <CardContent className={`
              flex flex-col items-center gap-3 p-4 md:p-6
              ${path === "/student/school-exam" ? "text-primary-foreground" : "group"}
            `}>
              <BookA className={`
                h-10 w-10 md:h-20 md:w-20
                ${path === "/student/school-exam" ? "" : "text-blue-900 group-hover:text-primary-foreground"}
              `} />
              <div className={`
                text-sm font-bold md:text-lg
                ${path === "/student/school-exam" ? "" : "text-foreground group-hover:text-primary-foreground"}
              `}>
                Ujian
              </div>
            </CardContent>
          </Card>
        </Link>
      </div>
    </div>
  )
}

export function HeroExam({ course, examTitle, expiryTimestamp }) {
  const { hours, minutes, seconds } = useTimer({ expiryTimestamp })

  return (
    <Card className="grid gap-3 p-6">
      <div className="flex items-baseline justify-between">
        <h2 className="text-lg font-bold">{examTitle} • {course}</h2>
        <div className="flex items-center gap-2">
          <Timer className="h-4 w-4" />
          <span>{`${addLeadingZero(hours)}:${addLeadingZero(minutes)}:${addLeadingZero(seconds)}`}</span>
        </div>
      </div>
    </Card>
  )
}

function addLeadingZero(num) {
  return num.toString().padStart(2, '0');
}