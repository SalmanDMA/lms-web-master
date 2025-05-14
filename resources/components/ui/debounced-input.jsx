import { useEffect, useState } from "react"
import { Input } from "@/components/ui/input"

const DebouncedInput = ({ value: initValue, onChange, debounce = 500, ...props }) => {
  const [value, setValue] = useState(initValue)

  useEffect(() => {
    setValue(initValue)
  }, [initValue])

  useEffect(() => {
    const timeout = setTimeout(() => {
      onChange(value)
    }, debounce)
    return () => clearTimeout(timeout)
  }, [value])

  return (
    <Input
      {...props}
      value={value}
      onChange={e => setValue(e.target.value)}
    />
  )
}

export default DebouncedInput