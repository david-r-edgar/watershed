export function getWaypointPopupText (posOS, posLL, name, distSoFar, distRemaining, note, prefix='') {
  const distSoFarText = distSoFar ? `<div>${(distSoFar / 1000).toFixed(1)} km from Dunnet Head</div>` : ''
  const distRemainingText = distRemaining ? `<div>${(distRemaining / 1000).toFixed(1)} km to Leathercote Point</div>` : ''
  const description = `<div>${distSoFarText}${distRemainingText}</div><p>${note}</p>`

  const geohackUrl = `http://toolserver.org/~geohack/geohack.php?pagename=${name}&params=${posLL[0]}_N_${posLL[1]}_E_region:GB_type:landmark`
  const bingUrl = `https://www.bing.com/maps/?&cp=${posLL[0]}~${posLL[1]}&lvl=15&sty=s`

  const waypointPopupText =
  `
<div><b>${prefix}${name}</b></div>
${description}
<p>grid ref: ${posOS[0]}, ${posOS[1]}</p>
<ul>
<li><a href=\"${geohackUrl}\" target='_blank'>geohack map sources</a></li>
<li><a href=\"${bingUrl}\" target='_blank'>bing OS 1:25,000</a></li>
</ul>
  `

  return waypointPopupText
}
