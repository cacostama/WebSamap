"use client"

import { useState } from 'react'
import Link from 'next/link'
import { Download, MessageCircle, ArrowLeft } from 'lucide-react'

import heroPlanImage from '@/assets/images/hero_slider2.png'
import { Button } from '@/components/ui/button'

const planes = [
  {
    slug: 'alfa',
    nombre: 'Alfa',
    precio: 150000,
    descripcion: 'Plan básico para vos',
    recomendado: 'Ideal para quienes buscan su primera cobertura.',
    coberturas: [
      'Consultas médicas hasta 20 al año',
      'Análisis de laboratorio',
      'Urgencias 24/7',
      'Internación básica',
      'Medicamentos con 20% de descuento',
    ],
    imagen: heroPlanImage,
  },
  {
    slug: 'beta',
    nombre: 'Beta',
    precio: 280000,
    descripcion: 'Plan familiar estándar',
    recomendado: 'Pensado para familias que necesitan equilibrio y respaldo.',
    highlighted: true,
    coberturas: [
      'Consultas médicas hasta 40 al año',
      'Análisis de laboratorio',
      'Urgencias 24/7',
      'Internación y cirugía',
      'Medicamentos con 40% de descuento',
      'Chequeo preventivo anual',
    ],
    imagen: heroPlanImage,
  },
  {
    slug: 'gamma',
    nombre: 'Gamma',
    precio: 420000,
    descripcion: 'Plan premium completo',
    recomendado: 'La opción más completa para seguimiento y prevención.',
    coberturas: [
      'Consultas médicas ilimitadas',
      'Análisis de laboratorio',
      'Urgencias 24/7',
      'Internación y cirugía',
      'Medicamentos con 60% de descuento',
      'Chequeo preventivo anual',
      'Odontología básica',
      'Oftalmología',
    ],
    imagen: heroPlanImage,
  },
]

export function PlanesContent() {
  const [selectedPlan, setSelectedPlan] = useState<string | null>(null)
  const currentPlan = planes.find(p => p.slug === selectedPlan)

  return (
    <section className="px-6 py-20">
      <div className="max-w-6xl mx-auto">
        
        {/* Plan selector cards - shown when no plan selected */}
        {!selectedPlan && (
          <>
            <div className="grid md:grid-cols-3 gap-6 mb-12">
              {planes.map((plan) => (
                <button
                  key={plan.slug}
                  onClick={() => setSelectedPlan(plan.slug)}
                  className={`p-8 rounded-xl border-2 text-left transition-all hover:shadow-lg ${
                    plan.highlighted 
                      ? 'border-brand-blue bg-white shadow-lg' 
                      : 'border-neutral-200 bg-white hover:border-brand-blue/50'
                  }`}
                >
                  {plan.highlighted && (
                    <span className="inline-block px-3 py-1 bg-brand-blue text-white text-xs rounded-full mb-4">
                      Más popular
                    </span>
                  )}
                  <h3 className="text-2xl text-brand-blue-dark mb-2">{plan.nombre}</h3>
                  <p className="text-neutral-500 mb-4">{plan.descripcion}</p>
                  <div className="text-3xl font-semibold text-brand-blue">
                    Gs. {plan.precio.toLocaleString('es-PY')}
                    <span className="text-sm font-normal text-neutral-400">/mes</span>
                  </div>
                  <p className="mt-4 text-sm text-neutral-600">{plan.recomendado}</p>
                  <div className="mt-6 text-brand-blue text-sm font-medium">
                    Ver detalles →
                  </div>
                </button>
              ))}
            </div>
            
            <div className="flex flex-wrap gap-3 text-sm text-neutral-600 justify-center">
              <span className="rounded-full bg-neutral-100 px-4 py-2">35+ años de trayectoria</span>
              <span className="rounded-full bg-neutral-100 px-4 py-2">Asesoría comercial directa</span>
              <span className="rounded-full bg-neutral-100 px-4 py-2">Respuesta por WhatsApp</span>
            </div>
          </>
        )}

        {/* Plan detail - shown when a plan is selected */}
        {selectedPlan && currentPlan && (
          <div className="animate-fade-in">
            <button
              onClick={() => setSelectedPlan(null)}
              className="flex items-center gap-2 text-neutral-600 hover:text-brand-blue mb-8 transition-colors"
            >
              <ArrowLeft className="h-4 w-4" />
              Volver a los planes
            </button>
            
            <div className="grid lg:grid-cols-[1fr_1.2fr] gap-12">
              {/* Plan image */}
              <div className="overflow-hidden rounded-2xl bg-neutral-100 h-80 flex items-center justify-center">
                <span className="text-neutral-400">Imagen del plan {currentPlan.nombre}</span>
              </div>
              
              {/* Plan info */}
              <div>
                <div className="flex items-start justify-between mb-6">
                  <div>
                    <h2 className="text-4xl text-brand-blue-dark mb-2">{currentPlan.nombre}</h2>
                    <p className="text-lg text-neutral-500">{currentPlan.descripcion}</p>
                  </div>
                  <div className="text-right">
                    <div className="text-4xl font-semibold text-brand-blue">
                      Gs. {currentPlan.precio.toLocaleString('es-PY')}
                    </div>
                    <span className="text-neutral-400">/mes</span>
                  </div>
                </div>
                
                <p className="text-brand-green font-medium mb-8">{currentPlan.recomendado}</p>
                
                <h3 className="text-lg font-semibold text-brand-blue-dark mb-4">Coberturas incluidas</h3>
                <ul className="space-y-3 mb-8">
                  {currentPlan.coberturas.map((cobertura) => (
                    <li key={cobertura} className="flex items-center gap-3 text-neutral-700">
                      <svg className="w-5 h-5 text-brand-green flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      {cobertura}
                    </li>
                  ))}
                </ul>
                
                <div className="flex flex-col sm:flex-row gap-4 pt-6 border-t">
                  <Button asChild variant="outline">
                    <a href={`/downloads/cobertura-${currentPlan.slug}.pdf`} download>
                      <Download className="h-4 w-4 mr-2" />
                      Ver más detalles del plan
                    </a>
                  </Button>
                  <Button asChild className="bg-brand-blue hover:bg-brand-blue-dark">
                    <Link href={`/contacto?plan=${currentPlan.nombre}`}>
                      <MessageCircle className="h-4 w-4 mr-2" />
                      Quiero asesoramiento
                    </Link>
                  </Button>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </section>
  )
}