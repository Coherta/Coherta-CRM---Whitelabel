const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('app/campaign/campaigns-workflow-builder.php', 'utf8');
// Compile all inline JavaScript after replacing server-rendered values.
for (const match of source.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)) {
  new vm.Script(match[1].replace(/<\?php[\s\S]*?\?>/g, 'null'));
}
function declaration(name) {
  const line = source.split('\n').find(line => line.trimStart().startsWith('function ' + name + '('));
  assert.ok(line, name);
  return line.trim();
}
const context = vm.createContext({});
vm.runInContext(`
let counter=0;
const uid=()=>String(++counter);
const NODE_TYPES={check_tech_stack:{label:'Check Tech Stack'},if_true:{label:'Do A'},if_false:{label:'Do B'},wait:{label:'Wait'},opportunity:{label:'Opportunity'}};
let workflow={nodes:[{id:'root',type:'opportunity',x:0,y:0,config:{}}],edges:[]};
const getNodeById=id=>workflow.nodes.find(n=>n.id===id);
const getOutgoingEdges=id=>workflow.edges.filter(e=>e.from===id);
${['titleToType','createNodeWithBranches','getAutoContinueTarget','sanitizeWorkflow','getNodeWarning','isDecisionType'].map(declaration).join('\n')}
const created=createNodeWithBranches('check_tech_stack',workflow.nodes[0],null);
const check=getNodeById(created.nodeId);
check.config.softwareName='Salesforce';
const roundTrip=sanitizeWorkflow(JSON.parse(JSON.stringify(workflow)));
globalThis.result={created,workflow,roundTrip,yes:getAutoContinueTarget('check_tech_stack',created,'yes'),no:getAutoContinueTarget('check_tech_stack',created,'no'),decision:isDecisionType('check_tech_stack'),blankWarning:getNodeWarning({type:'check_tech_stack',config:{softwareName:'  '}})};
`, context);
const r=JSON.parse(JSON.stringify(context.result));
assert.equal(r.decision,true);
assert.equal(r.workflow.nodes.length,4);
assert.equal(r.workflow.nodes.find(n=>n.id===r.yes).title,'Yes');
assert.equal(r.workflow.nodes.find(n=>n.id===r.no).title,'No');
assert.deepEqual(r.workflow.edges.filter(e=>e.from===r.created.nodeId).map(e=>e.label),['Yes','No']);
assert.deepEqual(r.roundTrip.nodes.find(n=>n.id===r.created.nodeId).config,{softwareName:'Salesforce',sourceField:'software_names',matchOperator:'contains',caseSensitive:false});
assert.ok(r.blankWarning);
console.log('PASS: inline JavaScript syntax, Yes/No branch creation, continuation selection, save/reload contract, empty software warning');
