import type { Metadata } from 'next'

import { Navbar } from '@/components/marketing/navbar'
import { Footer } from '@/components/marketing/footer'
import { PlanesContent } from './PlanesContent'

export const metadata: Metadata = {
  title: 'Planes de Salud | SAMAP - Medicina Prepaga del Sanatorio Adventista',
  description: 'Conocé los planes Alfa, Beta y Gamma de SAMAP. Coberturas claras para cada etapa de tu vida. Más de 43 años cuidando tu salud.',
}

export default function PlanesPage() {
  return (
    <div className="min-h-screen">
      <Navbar />
      
      <section className="bg-brand-blue-5 px-6 py-16">
        <div className="mx-auto max-w-6xl">
          <p className="text-sm uppercase tracking-[0.24em] text-brand-blue">Planes SAMAP</p>
          <h1 className="mt-4 text-4xl md:text-5xl text-brand-blue-dark">
            Elegí la cobertura clara, cercana y pensada para tu etapa de vida.
          </h1>
          <p className="mt-4 max-w-2xl text-lg text-neutral-600">
            Cada plan organiza el acceso a consultas, estudios, urgencias y seguimiento con una propuesta simple de entender.
          </p>
        </div>
      </section>

      <PlanesContent />

      {/* CTA Section */}
      <section className="px-6 pb-20">
        <div className="mx-auto grid max-w-6xl gap-8 overflow-hidden rounded-2xl bg-brand-blue text-white lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
          <div className="p-10 md:p-12">
            <p className="text-sm uppercase tracking-[0.24em] text-white/70">Acompañamiento comercial</p>
            <h2 className="mt-3 text-3xl">
              Si no sabés cuál elegir, te guiamos en pocos minutos.
            </h2>
            <p className="mt-4 max-w-xl text-white/80">
              Te ayudamos a comparar opciones según etapa familiar, presupuesto y tipo de uso esperado.
            </p>
          </div>
          <div className="h-full min-h-[280px] bg-neutral-700 flex items-center justify-center">
            <span className="text-white/50">Imagen equipo comercial</span>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  )
}