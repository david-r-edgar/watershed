const iconConfig = {
  wp: {
    iconUrl: 'transpSP.png',
    iconAnchor: [7, 20]
  },
  end: {
    iconUrl: 'yellowMarker.png',
    iconAnchor: [16, 32]
  },
  curPos: {
    iconUrl: 'currentHiker.png',
    iconAnchor: [16, 33]
  },
  watersource: {
    iconUrl: 'watersource.png',
    iconAnchor: [9, 28]
  },
  issue: {
    iconUrl: 'issue.png',
    iconAnchor: [10, 30]
  }
}
const icons = {}

export function getIcon(type) {
  if (!icons[type]) {
    icons[type] = L.icon(iconConfig[type])
  }
  return icons[type]
}
