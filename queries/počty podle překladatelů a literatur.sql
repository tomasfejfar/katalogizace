SELECT COUNT(i.itemKey), i.value, i.id, i.`itemKey`, lit.value, i.itemId, i.colId
FROM import AS i
RIGHT JOIN import AS trl ON (i.itemId = trl.itemId AND i.colId = trl.colId AND trl.col='700' AND trl.subcol='4' AND trl.value='trl')
LEFT JOIN import AS lit ON (i.itemId = lit.itemId AND lit.col='KGP')
WHERE i.col = '700' AND i.subcol='a'
GROUP BY i.value, lit.value
ORDER BY COUNT(i.itemKey) DESC, i.value, lit.value