"use client"

import { useEffect, useRef } from 'react'

export function SmoothScrollProvider({ children }: { children: React.ReactNode }) {
  const animationRef = useRef<number | null>(null)
  const targetRef = useRef(0)
  const currentRef = useRef(0)

  useEffect(() => {
    if (typeof window === 'undefined') return

    const isMobile = 'ontouchstart' in window && navigator.maxTouchPoints > 0
    if (isMobile) return

    const animate = () => {
      const diff = targetRef.current - currentRef.current

      if (Math.abs(diff) > 0.5) {
        const step = diff * 0.08
        currentRef.current += step
        window.scrollTo(0, currentRef.current)
        animationRef.current = requestAnimationFrame(animate)
      } else {
        animationRef.current = null
      }
    }

    const handleWheel = (e: WheelEvent) => {
      targetRef.current += e.deltaY * 0.3
      targetRef.current = Math.max(0, Math.min(targetRef.current, document.body.scrollHeight - window.innerHeight))

      if (!animationRef.current) {
        animationRef.current = requestAnimationFrame(animate)
      }
    }

    window.addEventListener('wheel', handleWheel, { passive: true })

    return () => {
      window.removeEventListener('wheel', handleWheel)
      if (animationRef.current) {
        cancelAnimationFrame(animationRef.current)
      }
    }
  }, [])

  return <>{children}</>
}