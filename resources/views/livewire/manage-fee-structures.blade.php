<div class="card">
    <div class="card-header">
        <h4 class="card-title">Fee structures per grade per term</h4>
    </div>
    <div class="card-body">
        <x-display-validation-errors/>
        @if ($message)
            <div class="alert alert-success my-3">{{$message}}</div>
        @endif
        <div class="md:grid md:grid-cols-2 gap-3">
            <x-select id="structure-class" name="classId" label="Class" wire:model.live="classId">
                @foreach ($classes as $class)
                    <option value="{{$class['id']}}">{{$class['name']}}</option>
                @endforeach
            </x-select>
            <x-select id="structure-semester" name="semesterId" label="Term" wire:model.live="semesterId">
                @foreach ($semesters as $semester)
                    <option value="{{$semester['id']}}">{{$semester['name']}}</option>
                @endforeach
            </x-select>
        </div>
        <x-loading-spinner/>
        <div wire:loading.remove.delay>
            <table class="border w-full my-4">
                <thead class="bg-muted">
                    <tr>
                        <th class="p-2 border text-left">Fee</th>
                        <th class="p-2 border text-right">Amount (KES)</th>
                        <th class="p-2 border text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lines ?? [] as $line)
                        <tr>
                            <td class="border p-2">{{$line['fee']['name'] ?? ''}}</td>
                            <td class="border p-2 text-right">{{number_format($line->amount->getAmount()->toFloat(), 2)}}</td>
                            <td class="border p-2 text-center">
                                <x-button label="Remove" theme="danger" wire:click="removeLine({{$line['id']}})" type="button"/>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="border p-4 text-center text-secondary">No fee lines yet for this class and term.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if (($lines ?? collect())->isNotEmpty())
                    <tfoot class="bg-muted font-bold">
                        <tr>
                            <td class="border p-2">Term total</td>
                            <td class="border p-2 text-right">{{number_format(($lines ?? collect())->sum(fn($l) => $l->amount->getAmount()->toFloat()), 2)}}</td>
                            <td class="border p-2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Add a fee line</h5>
                </div>
                <div class="card-body">
                    <form wire:submit="addLine" class="md:grid md:grid-cols-3 gap-3 items-end">
                        <x-select id="structure-fee" name="feeId" label="Fee" wire:model="feeId">
                            @foreach ($fees as $fee)
                                <option value="{{$fee['id']}}">{{$fee['name']}}</option>
                            @endforeach
                        </x-select>
                        <x-input id="structure-amount" name="amount" type="number" step="0.01" min="0" label="Amount (KES)" wire:model="amount"/>
                        <x-button label="Set Fee Line" theme="primary" icon="fas fa-plus" type="submit" class="w-full"/>
                    </form>
                </div>
            </div>

            <div class="flex my-3">
                <x-button label="Generate Term Invoices" theme="success" icon="fas fa-file-invoice-dollar" wire:click="generateInvoices" type="button"/>
            </div>
        </div>
    </div>
</div>
