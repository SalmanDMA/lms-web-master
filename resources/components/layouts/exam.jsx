import { useContext } from "react"
import { Head, Link, usePage } from "@inertiajs/react"
import { QuestionIndexContext } from "@/js/Pages/student/exam"

// ICONS
import {
  CircleUser,
  ChevronLeft,
  ChevronRight,
  LayoutDashboard,
} from "lucide-react"

// UI
import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Toaster } from "@/components/ui/toaster"

export default function ExamLayout({ children, title, totalQuestion, onSubmit }) {
  const { user } = usePage().props
  const [questionIndex, setQuestionIndex] = useContext(QuestionIndexContext)

  const questionIndexButtons = (questionIndexs) => {
    const answers = localStorage.getItem("answers") ? JSON.parse(localStorage.getItem("answers")) : ""
    let content = []

    for (let i = 1; i <= questionIndexs; i++) {
      content.push(
        <Button
          key={i}
          onClick={() => setQuestionIndex(i)}
          size="icon"
          variant={i == questionIndex ? "outline" : "default"}
          className={answers !== "" && answers[i - 1] !== null ? "border-2 border-green-500" : ""}
        >
          {i}
        </Button>
      )
    }

    return content
  }

  return (
    <>
      <Head title={`${title} - TMB Learning Management System`} />
      <div className="grid min-h-screen grid-cols-1 md:grid-cols-[220px_1fr] lg:grid-cols-[250px_1fr]">
        <div className="hidden border-r md:block">
          <div className="fixed flex h-full flex-col gap-2 bg-primary/90 md:w-[220px] lg:w-[250px]">
            <div className="flex h-14 items-center bg-primary px-4 text-primary-foreground lg:h-[60px] lg:px-6">
              <Link
                href="#"
                className="flex items-center gap-2 font-semibold"
              >
                <LayoutDashboard className="h-6 w-6" />
                <span>TMB LMS</span>
              </Link>
            </div>
            <div className="grid grid-cols-4 place-items-center gap-y-4 p-2">
              {questionIndexButtons(totalQuestion)}
            </div>
          </div>
        </div>
        <div className="flex flex-col">
          <header className="flex h-14 items-center gap-4 border-b bg-primary px-4 text-primary-foreground md:bg-muted/40 md:text-black lg:h-[60px] lg:px-6">
            <div className="w-full flex-1">
              <h1 className="text-sm font-semibold sm:text-lg">{title}</h1>
              <h2 className="text-xs">TMB Learning Management System</h2>
            </div>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="secondary" size="icon" className="rounded-full">
                  <CircleUser className="h-5 w-5" />
                  <span className="sr-only">Toggle user menu</span>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuLabel>
                  <p>{user.fullname}</p>
                  <p className="text-xs">{user.email}</p>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                  <Link
                    href="/logout"
                  >
                    Logout
                  </Link>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </header>
          <main className="grid gap-4 p-4 lg:p-6">
            {children}
            <div className="hidden w-full items-center justify-evenly md:flex">
              {questionIndex != 1 && (
                <Button onClick={() => setQuestionIndex(questionIndex - 1)}>
                  <ChevronLeft className="mr-2 h-4 w-4" />
                  Kembali
                </Button>
              )}
              {questionIndex != totalQuestion && (
                <Button onClick={() => setQuestionIndex(questionIndex + 1)}>
                  Selanjutnya
                  <ChevronRight className="ml-2 h-4 w-4" />
                </Button>
              )}
              {questionIndex == totalQuestion && (
                <Button variant="success" onClick={onSubmit}>
                  Submit
                </Button>
              )}
            </div>
            <div className="sticky bottom-4 flex flex-col items-center justify-center gap-3 md:hidden">
              <div className={`flex w-full items-center justify-${questionIndex != 1 ? "between" : "end"}`}>
                {questionIndex != 1 && (
                  <Button size="icon" onClick={() => setQuestionIndex(questionIndex - 1)}>
                    <ChevronLeft className="h-4 w-4" />
                  </Button>
                )}
                {questionIndex != totalQuestion && (
                  <Button size="icon" onClick={() => setQuestionIndex(questionIndex + 1)}>
                    <ChevronRight className="h-4 w-4" />
                  </Button>
                )}
              </div>
              <Card className="p-6">
                <div className="grid grid-cols-8 place-items-center gap-1 p-2">
                  {questionIndexButtons(totalQuestion)}
                </div>
              </Card>
            </div>
          </main>
          <Toaster />
        </div>
      </div>
    </>
  )
}
