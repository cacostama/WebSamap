"use client"

import { useState } from 'react'
import Image from 'next/image'
import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { Menu } from 'lucide-react'

import logo from '@/assets/images/logo.png'
import { Button } from '@/components/ui/button'
import { Sheet, SheetContent } from '@/components/ui/sheet'
import { ContactModal } from '@/components/marketing/ContactModal'

const navLinks = [
  { href: '#planes', label: 'Planes' },
  { href: '#beneficios', label: 'Beneficios' },
  { href: '#prestadores', label: 'Prestadores' },
  { href: '#blog', label: 'Blog' },
  { href: '#nosotros', label: 'Nosotros' },
]

export function Navbar() {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)
  const [contactModalOpen, setContactModalOpen] = useState(false)
  const pathname = usePathname()

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }

  const handleLogoClick = (e: React.MouseEvent) => {
    if (pathname === '/') {
      e.preventDefault()
      scrollToTop()
    }
  }

  const scrollToSection = (e: React.MouseEvent, href: string) => {
    e.preventDefault()
    const element = document.querySelector(href)
    if (!element) return

    const targetPosition = element.getBoundingClientRect().top + window.scrollY
    const startPosition = window.scrollY
    const duration = 800
    const startTime = performance.now()

    const easeOutQuart = (t: number) => 1 - Math.pow(1 - t, 4)

    const animateScroll = (currentTime: number) => {
      const elapsed = currentTime - startTime
      const progress = Math.min(elapsed / duration, 1)
      const easedProgress = easeOutQuart(progress)
      const currentPosition = startPosition + (targetPosition - startPosition) * easedProgress

      window.scrollTo(0, currentPosition)

      if (progress < 1) {
        requestAnimationFrame(animateScroll)
      }
    }

    requestAnimationFrame(animateScroll)
    setMobileMenuOpen(false)
  }

  return (
    <>
      <header className="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-neutral-100">
        <div className="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
          <Link href="/" onClick={handleLogoClick} className="flex items-center gap-2 cursor-pointer">
            <Image src={logo} alt="SAMAP" className="h-10 w-auto" priority />
          </Link>
          
          <nav className="hidden md:flex items-center gap-8">
            {navLinks.map((link) => (
              <a 
                key={link.href} 
                href={link.href}
                className="text-sm text-neutral-600 hover:text-brand-blue transition-colors cursor-pointer"
                onClick={(e) => scrollToSection(e, link.href)}
              >
                {link.label}
              </a>
            ))}
          </nav>
          
          {/* Desktop CTAs */}
          <div className="hidden md:flex items-center gap-3">
            <Button
              variant="outline"
              size="sm"
              onClick={() => setContactModalOpen(true)}
            >
              Cotizar
            </Button>
            <Button
              size="sm"
              className="bg-brand-blue hover:bg-brand-blue-dark"
              onClick={() => setContactModalOpen(true)}
            >
              Contacto
            </Button>
          </div>

          {/* Mobile: solo hamburger */}
          <Button
            variant="ghost"
            size="icon"
            className="md:hidden"
            onClick={() => setMobileMenuOpen(true)}
            aria-label="Abrir menú de navegación"
          >
            <Menu className="h-5 w-5" />
          </Button>
        </div>

        <Sheet open={mobileMenuOpen} setOpen={setMobileMenuOpen}>
          <SheetContent className="w-[280px]">
            <div className="flex flex-col gap-1 mt-4">
              {navLinks.map((link) => (
                <a 
                  key={link.href} 
                  href={link.href}
                  className="text-base text-neutral-700 hover:text-brand-blue py-2 px-3 rounded-md hover:bg-neutral-50 transition-colors cursor-pointer"
                  onClick={(e) => scrollToSection(e, link.href)}
                >
                  {link.label}
                </a>
              ))}
              <div className="flex flex-col gap-2 mt-4 pt-4 border-t">
                <Button 
                  variant="outline" 
                  size="sm"
                  onClick={() => {
                    setMobileMenuOpen(false)
                    setContactModalOpen(true)
                  }}
                >
                  Cotizar
                </Button>
                <Button 
                  size="sm" 
                  className="bg-brand-blue hover:bg-brand-blue-dark"
                  onClick={() => {
                    setMobileMenuOpen(false)
                    setContactModalOpen(true)
                  }}
                >
                  Contacto
                </Button>
              </div>
            </div>
          </SheetContent>
        </Sheet>
      </header>

      <ContactModal 
        open={contactModalOpen} 
        onClose={() => setContactModalOpen(false)} 
      />
    </>
  )
}