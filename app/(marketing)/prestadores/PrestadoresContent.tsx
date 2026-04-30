"use client"

import Image from 'next/image'
import { MapPin, Search, Stethoscope } from 'lucide-react'

import teamOne from '@/assets/images/team.png'
import teamTwo from '@/assets/images/team2.png'
import teamThree from '@/assets/images/team3.png'
import teamFour from '@/assets/images/team4.png'
import { Button } from '@/components/ui/button'

export function PrestadoresContent() {
  const especialidades = [
    'Medicina General',
    'Cardiología',
    'Pediatría',
    'Ginecología',
    'Dermatología',
    'Oftalmología',
    'Ortopedia',
    'Neurología',
    'Nutrición',
    'Psicología',
  ]

  const prestadores = [
    { nombre: 'Dr. Juan Pérez', especialidad: 'Medicina General', sede: 'Sanatorio Adventista', imagen: teamOne },
    { nombre: 'Dra. María González', especialidad: 'Cardiología', sede: 'Sanatorio Adventista', imagen: teamTwo },
    { nombre: 'Dr. Carlos Rodríguez', especialidad: 'Pediatría', sede: 'Sanatorio Adventista', imagen: teamThree },
    { nombre: 'Dra. Ana López', especialidad: 'Ginecología', sede: 'Sanatorio Adventista', imagen: teamFour },
    { nombre: 'Dr. Roberto Fernández', especialidad: 'Dermatología', sede: 'Sanatorio Adventista', imagen: teamOne },
    { nombre: 'Dra. Laura Martínez', especialidad: 'Oftalmología', sede: 'Sanatorio Adventista', imagen: teamTwo },
  ]

  return (
    <>
      {/* Specialty filters as cards */}
      <section className="py-12 px-6">
        <div className="max-w-6xl mx-auto">
          <h2 className="text-2xl text-brand-blue-dark mb-6">Especialidades disponibles</h2>
          <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
            {especialidades.map((esp) => (
              <button
                key={esp}
                className="p-4 rounded-xl border border-neutral-200 bg-white hover:border-brand-blue hover:shadow-md transition-all text-center"
              >
                <Stethoscope className="h-6 w-6 text-brand-blue mx-auto mb-2" />
                <span className="text-sm text-neutral-700">{esp}</span>
              </button>
            ))}
          </div>
        </div>
      </section>

      <section className="py-12 px-6 bg-neutral-50">
        <div className="max-w-6xl mx-auto">
          {/* Search bar */}
          <div className="mb-8">
            <div className="flex items-center gap-3 rounded-full border border-neutral-200 bg-white px-4 py-3">
              <Search className="h-5 w-5 text-neutral-400" />
              <input
                type="text"
                placeholder="Buscá por nombre o especialidad..."
                className="flex-1 bg-transparent outline-none text-neutral-700"
              />
            </div>
          </div>
          
          <div className="mb-6 flex items-center justify-between">
            <p className="text-sm text-neutral-500">
              Mostrando {prestadores.length} profesionales
            </p>
          </div>
          
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {prestadores.map((prestador) => (
              <div 
                key={prestador.nombre}
                className="flex gap-4 rounded-2xl border border-neutral-200 bg-white p-5 hover:shadow-md transition-shadow"
              >
                <Image 
                  src={prestador.imagen} 
                  alt={prestador.nombre} 
                  className="h-20 w-20 rounded-xl object-cover" 
                />
                <div className="flex flex-col justify-between">
                  <div>
                    <h3 className="text-base font-medium text-brand-blue-dark">{prestador.nombre}</h3>
                    <p className="mt-1 flex items-center gap-2 text-sm text-neutral-500">
                      <Stethoscope className="h-4 w-4" />
                      {prestador.especialidad}
                    </p>
                    <p className="mt-1 flex items-center gap-2 text-sm text-neutral-400">
                      <MapPin className="h-4 w-4" />
                      {prestador.sede}
                    </p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-20 px-6">
        <div className="max-w-3xl mx-auto text-center">
          <h2 className="text-2xl text-brand-blue-dark mb-4">
            ¿No encontrás lo que buscás?
          </h2>
          <p className="text-neutral-600 mb-8">
            Llamanos y te conectamos con el especialista indicado.
          </p>
          <Button asChild className="bg-brand-blue hover:bg-brand-blue-dark">
            <a href="/contacto">Contactar</a>
          </Button>
        </div>
      </section>
    </>
  )
}