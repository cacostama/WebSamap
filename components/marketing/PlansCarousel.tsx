"use client"

import { useRef, useState, useEffect, useCallback } from 'react'
import Link from 'next/link'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { Button } from '@/components/ui/button'

const planes = [
  { 
    name: 'Alfa', 
    slug: 'alfa', 
    price: 'Gs. 150.000', 
    desc: 'Plan básico individual',
    featured: false,
    recommended: 'Ideal para quienes buscan su primera cobertura',
  },
  { 
    name: 'Beta', 
    slug: 'beta', 
    price: 'Gs. 280.000', 
    desc: 'Plan familiar estándar',
    featured: true,
    recommended: 'Pensado para familias que necesitan equilibrio y respaldo',
  },
  { 
    name: 'Gamma', 
    slug: 'gamma', 
    price: 'Gs. 420.000', 
    desc: 'Plan premium completo',
    featured: false,
    recommended: 'La opción más completa para seguimiento y prevención',
  },
]

export function PlansCarousel() {
  const scrollRef = useRef<HTMLDivElement>(null)
  const [currentIndex, setCurrentIndex] = useState(1)
  const [isHovering, setIsHovering] = useState(false)
  const [scrollDirection, setScrollDirection] = useState<'left' | 'right' | null>(null)
  
  const handleMouseMove = useCallback((e: React.MouseEvent) => {
    if (!scrollRef.current) return
    
    const rect = scrollRef.current.getBoundingClientRect()
    const mouseY = e.clientY - rect.top
    const relativeY = mouseY / rect.height
    
    if (relativeY < 0.4) {
      setScrollDirection('left')
    } else if (relativeY > 0.6) {
      setScrollDirection('right')
    } else {
      setScrollDirection(null)
    }
  }, [])
  
  useEffect(() => {
    if (!isHovering || !scrollDirection) return
    
    const interval = setInterval(() => {
      if (scrollDirection === 'right') {
        setCurrentIndex(prev => {
          const maxIndex = planes.length - 1
          return prev >= maxIndex ? 0 : prev + 1
        })
      } else {
        setCurrentIndex(prev => {
          const maxIndex = planes.length - 1
          return prev <= 0 ? maxIndex : prev - 1
        })
      }
    }, 600)
    
    return () => clearInterval(interval)
  }, [isHovering, scrollDirection])
  
  const goToCard = (index: number) => {
    setCurrentIndex(index)
  }
  
  const scrollPrev = () => {
    setCurrentIndex(prev => prev <= 0 ? planes.length - 1 : prev - 1)
  }
  
  const scrollNext = () => {
    setCurrentIndex(prev => prev >= planes.length - 1 ? 0 : prev + 1)
  }

  return (
    <section id="planes" className="py-20 px-6 bg-white">
      <div className="max-w-6xl mx-auto">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl text-brand-blue-dark mb-4">
            Planes diseñados para vos
          </h2>
          <p className="text-neutral-600 max-w-lg mx-auto">
            Encontrá el plan que se adapta a vos y tu familia. Coberturas claras, sin letra chica.
          </p>
        </div>
        
        <div 
          ref={scrollRef}
          className="relative overflow-hidden rounded-2xl"
          onMouseEnter={() => setIsHovering(true)}
          onMouseLeave={() => {
            setIsHovering(false)
            setScrollDirection(null)
          }}
          onMouseMove={handleMouseMove}
          style={{ minHeight: '420px' }}
        >
          <div className="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none" />
          <div className="absolute right-0 top-0 bottom-0 w-24 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none" />
          
          <div className="flex gap-6 transition-transform duration-500 ease-out"
            style={{ transform: `translateX(-${currentIndex * 384}px)` }}>
            {planes.map((plan, index) => (
              <div
                key={`${plan.slug}-${index}`}
                className={`
                  relative flex-shrink-0 w-[360px] rounded-2xl p-8 transition-all duration-500
                  ${currentIndex === index 
                    ? 'bg-brand-blue text-white shadow-2xl scale-105 z-20' 
                    : index === (currentIndex - 1 + planes.length) % planes.length || index === (currentIndex + 1) % planes.length
                      ? 'bg-white border border-neutral-200 scale-100 z-0'
                      : 'bg-white border border-neutral-200 scale-95 opacity-50 z-0'
                  }
                `}
                style={{ marginTop: currentIndex === index ? '0' : '20px' }}
              >
                {plan.featured && currentIndex === index && (
                  <div className="absolute -top-3 left-1/2 -translate-x-1/2">
                    <span className="bg-brand-green text-white text-xs font-semibold px-4 py-1 rounded-full">
                      Más popular
                    </span>
                  </div>
                )}
                
                <h3 className={`text-2xl ${currentIndex === index ? 'text-white' : 'text-brand-blue-dark'}`}>
                  Plan {plan.name}
                </h3>
                
                <p className={`mt-1 text-sm ${currentIndex === index ? 'text-white/70' : 'text-neutral-500'}`}>
                  {plan.desc}
                </p>
                
                <div className={`mt-6 ${currentIndex === index ? 'text-white' : 'text-brand-blue'}`}>
                  <span className="text-4xl font-bold">{plan.price}</span>
                  <span className={`text-sm ${currentIndex === index ? 'text-white/60' : 'text-neutral-400'}`}>/mes</span>
                </div>
                
                <p className={`mt-4 text-xs ${currentIndex === index ? 'text-white/80' : 'text-neutral-500'}`}>
                  {plan.recommended}
                </p>
                
                <div className="mt-6 space-y-2">
                  {['Consultas médicas', 'Urgencias 24/7', 'Análisis de laboratorio'].map((item, i) => (
                    <div key={i} className="flex items-center gap-2">
                      <svg aria-hidden="true" className="w-4 h-4 text-brand-green flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      <span className={`text-sm ${currentIndex === index ? 'text-white/90' : 'text-neutral-700'}`}>{item}</span>
                    </div>
                  ))}
                </div>
                
                <Button
                  asChild
                  variant={currentIndex === index ? 'default' : 'outline'}
                  className={`mt-8 w-full ${
                    currentIndex === index 
                      ? 'bg-white text-brand-blue hover:bg-white/90' 
                      : ''
                  }`}
                >
                  <Link href={`/planes?plan=${plan.slug}`}>Ver coberturas</Link>
                </Button>
              </div>
            ))}
          </div>
          
          <button
            onClick={scrollPrev}
            className="absolute left-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-white shadow-lg flex items-center justify-center hover:bg-neutral-50 transition-colors"
            aria-label="Plan anterior"
          >
            <ChevronLeft className="w-6 h-6 text-brand-blue" />
          </button>
          <button
            onClick={scrollNext}
            className="absolute right-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-white shadow-lg flex items-center justify-center hover:bg-neutral-50 transition-colors"
            aria-label="Siguiente plan"
          >
            <ChevronRight className="w-6 h-6 text-brand-blue" />
          </button>
        </div>
        
        <div className="flex justify-center gap-3 mt-8">
          {planes.map((_, index) => (
            <button
              key={index}
              onClick={() => goToCard(index)}
              className={`
                w-3 h-3 rounded-full transition-all duration-300
                ${currentIndex === index 
                  ? 'bg-brand-blue w-8' 
                  : 'bg-neutral-300 hover:bg-neutral-400'
                }
              `}
              aria-label={`Ir al plan ${index + 1}`}
            />
          ))}
        </div>
        
        <p className="text-center mt-6 text-sm text-neutral-500">
          {isHovering 
            ? scrollDirection === 'right' 
              ? '↑ Moviendo al siguiente plan' 
              : scrollDirection === 'left' 
                ? '↓ Moviendo al plan anterior' 
                : 'Posicioná el cursor arriba o abajo para navegar'
            : 'Pasá el mouse sobre los planes y mové arriba o abajo para navegar'
          }
        </p>
        
        <div className="text-center mt-10">
          <Link href="/planes" className="text-brand-blue hover:underline text-sm font-medium">
            Ver todos los planes →
          </Link>
        </div>
      </div>
    </section>
  )
}