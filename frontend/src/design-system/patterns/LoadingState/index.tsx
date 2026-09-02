import { StatePattern, type StatePatternProps } from '../StatePattern';
import { Spinner } from '../../primitives/Spinner';
export function LoadingState({ title, ...props }: StatePatternProps) { return <StatePattern {...props} title={<><Spinner /> {title}</>} />; }
