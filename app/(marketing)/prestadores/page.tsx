import type { Metadata } from 'next'
import { FileText } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { Navbar } from '@/components/marketing/navbar'
import { Footer } from '@/components/marketing/footer'
import { PrestadoresContent } from './PrestadoresContent'

export const metadata: Metadata = {
  title: 'Guía Médica | SAMAP - Red de Prestadores',
  description: 'Encontrá especialistas y profesionales de la salud en nuestra red. Más de 100 prestadores en todo Paraguay.',
}

export default function PrestadoresPage() {
  return (
    <div className="min-h-screen">
      <Navbar />
      
      <section className="bg-brand-blue-5 px-6 py-16">
        <div className="mx-auto max-w-6xl grid lg:grid-cols-2 gap-10 items-center">
          <div>
            <p className="text-sm uppercase tracking-[0.24em] text-brand-blue">Guía Médica</p>
            <h1 className="mt-4 text-4xl md:text-5xl text-brand-blue-dark">
              Accedé a una red de profesionales para consultas, seguimiento y especialidades.
            </h1>
            <p className="mt-4 text-lg text-neutral-600">
              Encontrá rápidamente el especialista que necesitás.
            </p>
          </div>
          <div className="bg-white rounded-2xl p-6 shadow-lg">
            <h3 className="text-lg font-semibold text-brand-blue-dark mb-4">¿Qué estás buscando?</h3>
            <div className="space-y-4">
              <div className="flex gap-4">
                <Button asChild variant="outline" className="flex-1">
                  <a href="/downloads/guia-medica.pdf" download>
                    <FileText className="h-4 w-4 mr-2" />
                    Descargar Guía PDF
                  </a>
                </Button>
              </div>
              <p className="text-xs text-neutral-500 text-center">
                Descargá la guía médica completa con todos los prestadores
              </p>
            </div>
          </div>
        </div>
      </section>

      <PrestadoresContent />

      <Footer />
    </div>
  )
}