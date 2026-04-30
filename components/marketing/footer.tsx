import { Facebook, Instagram } from 'lucide-react'
import Link from 'next/link'
import { TELEFONO, FACEBOOK_URL, INSTAGRAM_URL } from '@/lib/constants'

export function Footer() {
  const currentYear = new Date().getFullYear()
  
  return (
    <footer className="bg-neutral-800 text-white">
      <div className="max-w-6xl mx-auto px-6 py-12">
        {/* Main columns - stacked on mobile, row on desktop */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12">
          
          {/* Column 1: Centro de Atención */}
          <div className="md:border-r md:border-neutral-600 md:pr-8">
            <h4 className="text-sm font-semibold uppercase tracking-wider mb-4 text-white/80">
              Centro de Atención
            </h4>
            <address className="not-italic text-xs text-neutral-400 space-y-2">
              <p>Pettirossi 380 esquina Pa&apos;i Pérez - Asunción</p>
              <p>Lunes a jueves: 07:30 - 18:00 hs</p>
              <p>Viernes: 07:30 - 17:00 hs</p>
              <p>Domingo - Visaciones: 07:00 - 12:00 hs</p>
              <p className="mt-3">
                <span className="text-neutral-500">Central telefónica:</span><br />
                <a href={`tel:${TELEFONO}`} className="text-white hover:text-brand-green transition-colors">
                  {TELEFONO}
                </a>
              </p>
            </address>
            <div className="mt-6">
              <h4 className="text-sm font-semibold uppercase tracking-wider mb-3 text-white/80">
                Seguinos en:
              </h4>
              <div className="flex gap-4">
                <a 
                  href={FACEBOOK_URL} 
                  target="_blank" 
                  rel="noopener noreferrer"
                  aria-label="Facebook de SAMAP"
                  className="text-neutral-400 hover:text-white transition-colors"
                >
                  <Facebook className="h-5 w-5" />
                </a>
                <a 
                  href={INSTAGRAM_URL} 
                  target="_blank" 
                  rel="noopener noreferrer"
                  aria-label="Instagram de SAMAP"
                  className="text-neutral-400 hover:text-white transition-colors"
                >
                  <Instagram className="h-5 w-5" />
                </a>
              </div>
            </div>
          </div>
          
          {/* Column 2: Institucional */}
          <div className="md:border-r md:border-neutral-600 md:px-8">
            <h4 className="text-sm font-semibold uppercase tracking-wider mb-4 text-white/80">
              Institucional
            </h4>
            <ul className="space-y-2 text-xs text-neutral-400">
              <li><Link href="#nosotros" className="hover:text-white transition-colors">Nosotros</Link></li>
              <li><Link href="/contacto" className="hover:text-white transition-colors">Trabaja con Nosotros</Link></li>
              <li><Link href="/contacto" className="hover:text-white transition-colors">Contacto</Link></li>
            </ul>
          </div>
          
          {/* Column 3: Sanatorio Exclusivo */}
          <div className="md:pl-8">
            <h4 className="text-sm font-semibold uppercase tracking-wider mb-4 text-white/80">
              Sanatorio Exclusivo
            </h4>
            <div className="flex flex-wrap gap-4">
              <a 
                href="https://sanatorioadventista.com.py" 
                target="_blank" 
                rel="noopener noreferrer"
                className="block hover:opacity-80 transition-opacity"
              >
                <div className="bg-white rounded-lg p-3 w-fit">
                  <p className="text-xs text-brand-blue font-semibold text-center">Sanatorio Adventista</p>
                  <p className="text-[10px] text-neutral-500 text-center">de Asunción</p>
                </div>
              </a>
              <div className="bg-white rounded-lg p-3 w-fit">
                <p className="text-xs text-brand-blue font-semibold text-center">Iglesia Adventista</p>
                <p className="text-[10px] text-neutral-500 text-center">del 7mo Día</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      {/* Bottom bar */}
      <div className="border-t border-neutral-700">
        <div className="max-w-6xl mx-auto px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-2">
          <p className="text-xs text-neutral-500">
            © {currentYear} SAMAP S.A. - Medicina Prepaga del Sanatorio Adventista
          </p>
          <div className="flex gap-4 text-xs text-neutral-500">
            <Link href="/privacidad" className="hover:text-white transition-colors">Política de Privacidad</Link>
            <Link href="/terminos" className="hover:text-white transition-colors">Términos y Condiciones</Link>
          </div>
        </div>
      </div>
    </footer>
  )
}
