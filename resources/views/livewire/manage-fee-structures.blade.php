<div class="card">
    <div class="card-header">
        <h4 class="card-title">Fee structures per grade per term</h4>
    </div>
    <div class="card-body">
        <x-display-validation-errors/>
        @if ($message)
            <div class="alert alert-success">{{$message}}</div>
        @endif
        <div class="md:grid grid-cols-2 gap-2">
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
            <table class="border w-full my-3">
                <thead>
                    <th class="p-2 border">Fee</th>
                    <th class="p-2 border">Amount (KES)</th>
                    <th class="p-2 border">Action</th>
                </thead>
                <tbody>
                    @forelse ($lines ?? [] as $line)
                        <tr>
                            <td class="border p-2">{{$line['fee']['name'] ?? ''}}</td>
                            <td class="border p-2">{{number_format($line->amount->getAmount()->toFloat(), 2)}}</td>
                            <td class="border p-2">
                                <x-button label="Remove" theme="danger" wire:click="removeLine({{$line['id']}})" type="button"/>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="border p-2 text-center">No fee lines yet for this class and term.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <form wire:submit="addLine" class="md:grid grid-cols-3 gap-2 items-end">
                <x-select id="structure-fee" name="feeId" label="Fee" wire:model="feeId">
                    @foreach ($fees as $fee)
                        <option value="{{$fee['id']}}">{{$fee['name']}}</option>
                    @endforeach
                </x-select>
                <x-input id="structure-amount" name="amount" type="number" step="0.01" min="0" label="Amount (KES)" wire:model="amount"/>
                <x-button label="Set Fee Line" theme="primary" type="submit"/>
            </form>
            <div class="my-3">
                <x-button label="Generate Term Invoices" theme="success" wire:click="generateInvoices" type="button"/>
            </div>
        </div>
    </div>
</div>
