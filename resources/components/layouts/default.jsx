import { Head, Link, usePage } from "@inertiajs/react"

// ICONS
import { CircleUser } from "lucide-react"

// UI
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Toaster } from "@/components/ui/toaster"

export default function DefaultLayout({ children, title, padding = true }) {
  const { role, user } = usePage().props

  return (
    <>
      <Head title={`${title} - TMB Learning Management System`} />
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
                <p>{
                  role === "ADMIN" || role === "STAFF" ?
                    user.name :
                    user.fullname
                }</p>
                <p className="text-xs">{user.email}</p>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem>Settings</DropdownMenuItem>
              <DropdownMenuItem asChild>
                <Link
                  href={
                    role === "ADMIN" || role === "STAFF" ?
                      "/admin/logout" :
                      "/logout"
                  }
                >
                  Logout
                </Link>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </header>
        <main className={padding ? "p-4 lg:p-6" : ""}>
          {children}
        </main>
        <Toaster />
      </div>
    </>
  )
}
