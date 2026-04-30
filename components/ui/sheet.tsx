"use client"

import * as React from "react"
import { X } from "lucide-react"
import { cn } from "@/lib/utils/cn"
import { Button } from "./button"

interface SheetContextValue {
  open: boolean
  setOpen: React.Dispatch<React.SetStateAction<boolean>>
}

const SheetContext = React.createContext<SheetContextValue | null>(null)

function useSheetContext() {
  const context = React.useContext(SheetContext)
  if (!context) throw new Error("Sheet components must be used within a Sheet")
  return context
}

function Sheet({ children, open, setOpen }: { children: React.ReactNode; open: boolean; setOpen: React.Dispatch<React.SetStateAction<boolean>> }) {
  return (
    <SheetContext.Provider value={{ open, setOpen }}>
      {children}
    </SheetContext.Provider>
  )
}

function SheetTrigger({ children }: { children: React.ReactNode }) {
  return <>{children}</>
}

function SheetContent({ children, className }: { children: React.ReactNode; className?: string }) {
  const { open, setOpen } = useSheetContext()

  React.useEffect(() => {
    if (open) {
      document.body.style.overflow = "hidden"
    } else {
      document.body.style.overflow = ""
    }
    return () => { document.body.style.overflow = "" }
  }, [open])

  if (!open) return null

  return (
    <>
      <div 
        className="fixed inset-0 z-50 bg-black/50 animate-in fade-in duration-200"
        onClick={() => setOpen(false)}
      />
      <div className={cn(
        "fixed inset-y-0 left-0 z-50 w-full max-w-sm bg-white shadow-xl animate-in slide-in-from-left duration-300",
        className
      )}>
        <div className="flex items-center justify-between p-4 border-b">
          <span className="font-semibold text-brand-blue">Menú</span>
          <Button variant="ghost" size="icon" onClick={() => setOpen(false)}>
            <X className="h-5 w-5" />
          </Button>
        </div>
        <nav className="p-4 flex flex-col gap-1">
          {children}
        </nav>
      </div>
    </>
  )
}

function SheetTitle({ children }: { children: React.ReactNode }) {
  return <>{children}</>
}

export { Sheet, SheetTrigger, SheetContent, SheetTitle }