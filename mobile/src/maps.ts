export type Coords = { lat: number; lng: number }

/**
 * Google Maps without an API key. `output=embed` is the documented iframe
 * endpoint, and the `?api=1` search URL hands off to the Maps app when one is
 * installed. Neither is billed or needs a key.
 */
export function embedUrl(point: Coords, zoom = 17): string {
  return `https://maps.google.com/maps?q=${point.lat},${point.lng}&z=${zoom}&output=embed`
}

export function openUrl(point: Coords): string {
  return `https://www.google.com/maps/search/?api=1&query=${point.lat},${point.lng}`
}

/** Walking/driving route between the two punches, for a quick sanity check. */
export function directionsUrl(from: Coords, to: Coords): string {
  return `https://www.google.com/maps/dir/?api=1&origin=${from.lat},${from.lng}&destination=${to.lat},${to.lng}`
}

export function formatCoords(point: Coords): string {
  return `${point.lat.toFixed(5)}, ${point.lng.toFixed(5)}`
}

/**
 * Straight-line distance in metres, so an admin can see at a glance whether
 * the two punches came from roughly the same place.
 */
export function distanceMetres(a: Coords, b: Coords): number {
  const R = 6371000
  const toRad = (deg: number) => (deg * Math.PI) / 180

  const dLat = toRad(b.lat - a.lat)
  const dLng = toRad(b.lng - a.lng)

  const h =
    Math.sin(dLat / 2) ** 2 +
    Math.cos(toRad(a.lat)) * Math.cos(toRad(b.lat)) * Math.sin(dLng / 2) ** 2

  return Math.round(2 * R * Math.asin(Math.sqrt(h)))
}

export function formatDistance(metres: number): string {
  return metres < 1000 ? `${metres} m apart` : `${(metres / 1000).toFixed(2)} km apart`
}
