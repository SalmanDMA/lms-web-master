// UI
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion"

export function AccordionNavLink({ children, icon, path, regex, title }) {
  return (
    <Accordion type="single" defaultValue={regex.test(path) ? title : ""} collapsible>
      <AccordionItem value={title} className="border-b-0">
        <AccordionTrigger
          className={`
            rounded-lg px-3 py-2
            transition-all hover:no-underline
            ${regex.test(path)
              ? "text-primary-foreground bg-primary/60"
              : "text-primary-foreground/60 hover:bg-primary/60 hover:text-primary-foreground"
            }
          `}
        >
          <div className="flex items-center gap-3">
            {icon}
            {title}
          </div>
        </AccordionTrigger>
        <AccordionContent className="grid items-start gap-2 pl-7 pt-3">
          {children}
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  )
}

export function AccordionNavLinkMobile({ children, icon, path, regex, title }) {
  return (
    <Accordion type="single" defaultValue={regex.test(path) ? title : ""} collapsible>
      <AccordionItem value={title} className="border-b-0">
        <AccordionTrigger
          className={`
            mx-[-0.65rem] flex items-center
            gap-4 rounded-xl px-3 py-2 hover:no-underline
            ${regex.test(path)
              ? "text-primary-foreground bg-primary"
              : "text-foreground"
            }
          `}
        >
          <div className="flex items-center gap-4">
            {icon}
            {title}
          </div>
        </AccordionTrigger>
        <AccordionContent className="grid items-start gap-2 pl-8 pt-3">
          {children}
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  )
}

export function NavLink({ children, href, path }) {
  return (
    <a
      href={href}
      className={`
        flex items-center gap-3 rounded-lg
        px-3 py-2 transition-all
        ${path === href
          ? "text-primary-foreground bg-primary/60"
          : "text-primary-foreground/60 hover:bg-primary/60 hover:text-primary-foreground"
        }
      `}
    >
      {children}
    </a>
  )
}

export function NavLinkMobile({ children, href, path }) {
  return (
    <a
      href={href}
      className={`
        mx-[-0.65rem] flex items-center
        gap-4 rounded-xl px-3 py-2
        ${path === href
          ? "text-primary-foreground bg-primary"
          : "text-foreground"
        }
      `}
    >
      {children}
    </a>
  )
}