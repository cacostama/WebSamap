import type { Metadata } from 'next'
import Image from 'next/image'
import Link from 'next/link'
import {
  Activity,
  Ambulance,
  Microscope,
  Pill,
  ShieldPlus,
  Stethoscope,
} from 'lucide-react'

import aboutImage from '@/assets/images/about_img.png'
import serviceOne from '@/assets/images/service.png'
import serviceThree from '@/assets/images/service3.png'
import serviceTwo from '@/assets/images/service2.png'
import { Button } from '@/components/ui/button'
import { Navbar } from '@/components/marketing/navbar'
import { Footer } from '@/components/marketing/footer'

export const metadata: Metadata = {
  title: 'Beneficios | SAMAP - Coberturas y Servicios Médicos',
  description: 'Consultas médicas, análisis de laboratorio, urgencias 24/7, internación y más. Conocé todos los beneficios de SAMAP.',
}

export default function BeneficiosPage() {
  const beneficios = [
    {
      titulo: 'Consultas Médicas',
      descripcion: 'Atención en consultorio con nuestros médicos especialistas.',
      icono: Stethoscope,
    },
    {
      titulo: 'Análisis de Laboratorio',
      descripcion: 'Exámenes de laboratorio con resultados rápidos y confiables.',
      icono: Microscope,
    },
    {
      titulo: 'Urgencias 24/7',
      descripcion: 'Atención de emergencias cualquier día y hora.',
      icono: Ambulance,
    },
    {
      titulo: 'Internación',
      descripcion: 'Habitaciones equipadas y atención especializada.',
      icono: ShieldPlus,
    },
    {
      titulo: 'Cirugías',
      descripcion: 'Procedimientos quirúrgicos con equipo de última generación.',
      icono: Activity,
    },
    {
      titulo: 'Medicamentos',
      descripcion: 'Descuentos en medicamentos de nuestra farmacia.',
      icono: Pill,
    },
  ]

  const serviceImages = [serviceOne, serviceTwo, serviceThree]

  return (
    <div className="min-h-screen">
      <Navbar />
      
      <section className="bg-brand-blue-5 px-6 py-16">
        <div className="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[1fr_0.9fr] lg:items-center">
          <div>
            <p className="text-sm uppercase tracking-[0.24em] text-brand-blue">Beneficios</p>
            <h1 className="mt-4 text-4xl md:text-5xl text-brand-blue" style={{ fontFamily: 'DM Serif Display, serif' }}>
              Coberturas pensadas para acompanarte en el uso real de tu salud.
            </h1>
            <p className="mt-4 max-w-2xl text-lg text-neutral-600">
              La propuesta combina consultas, estudios, urgencias y seguimiento preventivo para que resolver tu atencion sea mas simple.
            </p>
          </div>
          <div className="overflow-hidden rounded-[2rem] border border-white/80 bg-white p-3 shadow-[0_24px_60px_rgba(39,71,103,0.12)]">
            <Image src={aboutImage} alt="Atencion medica y acompanamiento familiar" className="h-auto w-full rounded-[1.5rem] object-cover" priority />
          </div>
        </div>
      </section>

      <section className="px-6 py-20">
        <div className="max-w-6xl mx-auto grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          {beneficios.map((beneficio, index) => {
            const Icon = beneficio.icono

            return (
            <div 
              key={beneficio.titulo}
              className="overflow-hidden rounded-[1.5rem] border border-neutral-200 bg-white shadow-sm transition-shadow hover:shadow-md"
            >
              <Image
                src={serviceImages[index % serviceImages.length]!}
                alt={beneficio.titulo}
                className="h-44 w-full object-cover"
              />
              <div className="p-6">
                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-blue-5 text-brand-blue">
                  <Icon className="h-5 w-5" />
                </div>
                <h3 className="mb-2 text-xl text-brand-blue-dark" style={{ fontFamily: 'DM Serif Display, serif' }}>
                  {beneficio.titulo}
                </h3>
                <p className="text-neutral-600">{beneficio.descripcion}</p>
              </div>
            </div>
            )
          })}
        </div>
      </section>

      <section className="py-16 px-6 bg-neutral-50">
        <div className="max-w-3xl mx-auto text-center">
          <h2 className="text-2xl text-brand-blue-dark mb-4" style={{ fontFamily: 'DM Serif Display, serif' }}>
            ¿Querés conocer más detalles?
          </h2>
          <p className="text-neutral-600 mb-8">
            Consultanos para recibir el detalle completo de coberturas y condiciones.
          </p>
          <Button asChild className="bg-brand-blue hover:bg-brand-blue-dark">
            <Link href="/contacto">Contactar</Link>
          </Button>
        </div>
      </section>

      <Footer />
    </div>
  )
}
