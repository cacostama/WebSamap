"use client"

import { useEffect, useCallback, useState } from 'react'
import { X } from 'lucide-react'
import { Button } from './button'

interface ModalProps {
  open: boolean
  onClose: () => void
  children: React.ReactNode
  title?: string
}

export function Modal({ open, onClose, children, title }: ModalProps) {
  const [isClosing, setIsClosing] = useState(false)
  
  const handleEscape = useCallback((e: KeyboardEvent) => {
    if (e.key === 'Escape') onClose()
  }, [onClose])

  useEffect(() => {
    if (open) {
      document.addEventListener('keydown', handleEscape)
      document.body.style.overflow = 'hidden'
    }
    return () => {
      document.removeEventListener('keydown', handleEscape)
      document.body.style.overflow = ''
    }
  }, [open, handleEscape])

  const handleClose = () => {
    setIsClosing(true)
    setTimeout(() => {
      setIsClosing(false)
      onClose()
    }, 200)
  }

  if (!open) return null

  return (
    <div 
      className="fixed inset-0 z-[100] flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby={title ? 'modal-title' : undefined}
    >
      <div 
        className={`absolute inset-0 bg-black/50 ${isClosing ? 'animate-backdrop-out' : 'animate-backdrop-in'}`}
        onClick={handleClose}
        aria-hidden="true"
      />
      <div className={`relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto ${isClosing ? 'animate-modal-out' : 'animate-modal-in'}`}>
        <div className="sticky top-0 bg-white z-10 flex items-center justify-between p-6 border-b">
          {title && <h2 id="modal-title" className="text-xl text-brand-blue-dark">{title}</h2>}
          <Button variant="ghost" size="icon" onClick={handleClose} aria-label="Cerrar">
            <X className="h-5 w-5" />
          </Button>
        </div>
        <div className="p-6">
          {children}
        </div>
      </div>
    </div>
  )
}