<tbody>
    <?php if (!empty($history_records)): ?>
        <?php $no = 1; foreach($history_records as $row): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><strong><?= htmlspecialchars($row['item_to_borrow'] ?? 'N/A'); ?></strong></td>
                <td><?= htmlspecialchars($row['return_date'] ?? 'N/A'); ?></td>
                <td>
                    <?php 
                        $st = strtolower($row['status'] ?? '');
                        
                        if (strpos($st, 'good') !== false): 
                    ?>
                        <!-- Kulay Green para sa Good - Clickable para ma-clear -->
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to clear this item?');">
                            <input type="hidden" name="clear_item_id" value="<?= $row['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-success text-white fw-bold" style="font-size: 11px; padding: 3px 12px;">Good (Clear)</button>
                        </form>
                    <?php elseif (strpos($st, 'replaced') !== false): ?>
                        <!-- Kulay Yellow para sa Replaced - Clickable para ma-clear -->
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to clear this item?');">
                            <input type="hidden" name="clear_item_id" value="<?= $row['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold" style="font-size: 11px; padding: 3px 12px;">Replaced (Clear)</button>
                        </form>
                    <?php elseif (strpos($st, 'damage') !== false): ?>
                        <!-- Kulay Red para sa Damaged - HINDI clickable para manatili sa dashboard -->
                        <span class="badge bg-danger text-white px-3 py-2" style="font-size: 11px;">Damaged (Pending)</span>
                    <?php else: ?>
                        <span class="badge bg-secondary text-white px-3 py-2" style="font-size: 11px;"><?= htmlspecialchars($row['status'] ?? 'Pending'); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" class="text-center text-muted py-4">No borrowing history records found.</td>
        </tr>
    <?php endif; ?>
</tbody>
